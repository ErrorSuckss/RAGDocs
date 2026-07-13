import json
import os
import uuid
import hmac
import hashlib
import chromadb
from fastapi import FastAPI, Form, UploadFile
from pydantic import BaseModel
from pypdf import PdfReader
from docx import Document
import io
import logging
import requests
from datetime import datetime

app = FastAPI()

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(levelname)s [%(name)s] %(message)s",
)

chroma_client = chromadb.PersistentClient(path="./chroma_db")
collection = chroma_client.get_or_create_collection(name="docs")
OLLAMA_API_KEY = os.getenv("OLLAMA_API_KEY") 
LARAVEL_API_KEY = os.getenv("LARAVEL_SECRET_KEY")

@app.post("/python-rag")
def home(data: dict):
  text = data.get("text")
  return {
      "message": "from fastAPI"
  }


def extract_text(filename: str, raw: bytes) -> str:
  ext = filename.lower().rsplit('.', 1)[-1]
  if ext == 'pdf':
    reader = PdfReader(io.BytesIO(raw))
    return "\n".join(page.extract_text() or "" for page in reader.pages)
  if ext == "docx":
        doc = Document(io.BytesIO(raw))
        return "\n".join(p.text for p in doc.paragraphs)

  if ext == "doc":
      raise ValueError("Legacy .doc not supported directly — convert to .docx first")

  if ext == "txt":
      return raw.decode("utf-8", errors="ignore")

  raise ValueError(f"Unsupported file type: {ext}")



def chunk_text(text, chunk_size=800, overlap=100):
  chunks = []
  start = 0
  while start < len(text):
      end = min(start + chunk_size, len(text))
      chunks.append(text[start:end])
      start += chunk_size - overlap
  return chunks




class QueryRequest(BaseModel):
    query: str

@app.post("/query")
async def query_docs(request: QueryRequest):
    logging.info('entering query docs')

    response = requests.post(
        "http://localhost:11434/api/embed",
        json={
            "model": "qwen3-embedding:4b",
            "input": request.query,
        },
        timeout=180,
    )

    response.raise_for_status()
    data = response.json()
    query_embedding = data["embeddings"][0]

    results = collection.query(
        query_embeddings=[query_embedding],
        n_results=4,
    )

    context = "\n\n".join(results["documents"][0])

    prompt  = f"""Answer the question using only the context below.
        Today's date: {datetime.now().strftime("%B %d, %Y")}

        Context:
        {context}

        Question: {request.query}
        """
    generated_answer = generate_answer(prompt)
    return {"answer": generated_answer}
  



def generate_answer(prompt: str) -> str:
  logging.info('generating an answer')
  try:
    response = requests.post(
        "https://ollama.com/api/chat",
        headers={
            "Authorization": f"Bearer {OLLAMA_API_KEY}"
        },
        json={
            "model": "gemma4:31b-cloud",
            "messages": [
                {"role": "user", "content": prompt}
            ],
            "stream": False,
            "temperature": 0.2,
        },
        timeout=180,
    )

    response.raise_for_status()
    logging.info('done answering')
    data = response.json()

    return data["message"]["content"]
    
  except Exception:
    logging.exception('Error generating an answer')
    return "An error occurred while generating the answer."



class DeleteRequest(BaseModel):
    file_id: int
    file_name: str

@app.delete('/delete')
async def delete_file(data:DeleteRequest):
    logging.info(f'deleting file {data.file_name} with file_id: {data.file_id}')
    results = collection.get(
        where={"file_id": data.file_id},
        include=[]
    )

    count = len(results["ids"])

    logging.info(
        f"Deleting file '{data.file_name}' (file_id={data.file_id}). "
        f"Found {count} embeddings to delete."
    )

    collection.delete(where={"file_id": data.file_id})

    logging.info(
        f"Deleted {count} embeddings for file '{data.file_name}' (file_id={data.file_id})."
    )


@app.post('/upload')
async def upload_file(file: UploadFile,file_id: int = Form(...),file_name: str = Form(...)):
    logging.info(f'{file_name} received!')
    raw = await file.read()
    process_and_embed(file_id, file_name, raw)
    return {'status': 'Accepted', 'file_id': file_id}


def process_and_embed(file_id: int, filename: str, raw: bytes):
    logging.info('Embedding file')
    added_ids = []
    try:
        text = extract_text(filename, raw)

        if not text.strip():
           logging.error(f"No text extracted from {filename}. Skipping embedding.")
           raise ValueError(f"No text extracted from {filename}. Skipping embedding.")

        for attempt, chunk in enumerate(chunk_text(text), start=1):
            response = requests.post(
                "http://localhost:11434/api/embed",
                json={
                    "model": "qwen3-embedding:4b",
                    "input": chunk,
                },
                timeout=180,
            )
            response.raise_for_status()
            embedding = response.json()["embeddings"][0]

            chunk_id = str(uuid.uuid4())
            try:
                collection.add(
                    ids=[chunk_id],
                    embeddings=[embedding],
                    documents=[chunk],
                    metadatas=[{"source": filename, "file_id": file_id}],
                )
                added_ids.append(chunk_id)
                logging.info(f'Storing chunk {attempt} successful!')
            except Exception as e:
                logging.exception('Storing embeddings error')
        notify_laravel(file_id, "completed")

    except Exception as e:
        logging.exception('Process and embedding error')
        if added_ids:
            collection.delete(ids=added_ids)
        notify_laravel(file_id, "failed", str(e))


def notify_laravel(file_id: int, status: str, error: str = None):
    logging.info('entering notify laravel function')
    if error:
       logging.warning(f'notifying laravel for error: {error}')
    else:
       logging.debug(f'notifying laravel with status: {status}')
       
    payload = {
        "file_id": file_id,
        "status": status,
        "error": error,
    }

    body = json.dumps(payload, separators=(',', ':'), sort_keys=True)

    signature = hmac.new(
        LARAVEL_API_KEY.encode(),
        body.encode(),
        hashlib.sha256
    ).hexdigest()

    try:
        response = requests.post(
            "http://127.0.0.1:8000/api/embedding-callback",
            headers={
                "Content-Type": "application/json",
                "X-Signature": signature
            },
            data=body,
            timeout=10
        )

        response.raise_for_status() 

        logging.info("Laravel callback succeeded.")

    except requests.exceptions.RequestException:
        logging.exception('Laravel callback failed')


       