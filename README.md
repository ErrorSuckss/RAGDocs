# rag-docs

A document management and AI chat platform that lets you upload documents (PDF, DOCX), automatically vectorizes them for retrieval using a locally hosted model, and lets you chat with your data through a RAG (Retrieval-Augmented Generation) pipeline — no third-party API keys required.

## Features

- 📄 **Document Upload** — Upload PDF and DOCX files with drag-and-drop support
- 🔄 **Real-time Status Updates** — Live processing status (Pending → Processing → Completed) via Laravel Reverb
- 💬 **AI Chat** — Ask questions and get answers grounded in your uploaded documents, powered by a self-hosted Ollama model
- 🔍 **Vector Search** — Document chunks and embeddings stored in ChromaDB for retrieval
- 📊 **Document Management** — View, share, and delete uploaded files with pagination
- 🎨 **Modern UI** — Built with Vue 3, Inertia.js, and Tailwind CSS

## Tech Stack

- **Backend:** Laravel
- **Frontend:** Vue 3 (Composition API) + Inertia.js
- **Styling:** Tailwind CSS
- **Real-time:** Laravel Reverb (WebSockets)
- **Database:** MySQL (raw SQL, no Laravel migrations)
- **Vector Store:** ChromaDB (persistent local client)
- **AI Runtime:** Ollama
  - Chat model: `gemma4:31b`
  - Embedding model: `qwen3-embedding:4b`
- **HTTP Client:** Axios

## Prerequisites

- PHP ^8.2
- Composer
- Node.js 18+ and npm
- MySQL
- Python 3.9+ (for the ChromaDB/embedding pipeline)
- [Ollama](https://ollama.com) installed and running locally, with the required models pulled:

  ```bash
  ollama pull gemma4:31b
  ollama pull qwen3-embedding:4b
  ```

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/ErrorSuckss/RAGDocs.git
   cd rag-docs
   ```

2. **Install PHP dependencies**

   ```bash
   cd laravel
   composer install
   ```

3. **Install JavaScript dependencies**

   ```bash
   npm install
   ```

4. **Set up the rag-service (Python)**

   ```bash
   cd ../rag-service
   python -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   pip install -r requirements.txt
   ```

5. **Set up environment variables**

   ```bash
   cd ../laravel
   cp .env.example .env
   php artisan key:generate
   ```

   Update `.env` with your MySQL credentials and Reverb settings.

6. **Create the database and table**

   Create a MySQL database, then run the following:

   ```sql
   CREATE DATABASE rag_docs;

   USE rag_docs;

   CREATE TABLE files (
       id INT(11) NOT NULL AUTO_INCREMENT,
       name VARCHAR(50) NOT NULL,
       file_path VARCHAR(255) NOT NULL,
       file_type VARCHAR(255) DEFAULT NULL,
       status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL,
       PRIMARY KEY (id)
   );
   ```

7. **Link storage** (for uploaded files)

   ```bash
   php artisan storage:link
   ```

8. **Build frontend assets**

   ```bash
   npm run dev
   ```

9. **Start the development server**

   ```bash
   php artisan serve
   ```

   Visit `http://localhost:8000`.

10. **Start the queue worker** (for document processing/vectorization)

    ```bash
    php artisan queue:work
    ```

11. **Start Laravel Reverb** (for real-time status updates)

    ```bash
    php artisan reverb:start
    ```

12. **Make sure Ollama is running** before uploading documents, since both vectorization and chat depend on it:

    ```bash
    ollama serve
    ```

13. **Start the rag-service** (Python) in a separate terminal:

    ```bash
    cd rag-service
    source venv/bin/activate  # On Windows: venv\Scripts\activate
    # adjust this to however you actually run it, e.g.:
    python app.py
    # or: uvicorn main:app --reload
    ```

## Environment Variables

Key variables to configure in `.env`:

```env
APP_NAME="rag-docs"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rag_docs
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Ollama
OLLAMA_HOST=http://localhost:11434
OLLAMA_CHAT_MODEL=gemma4:31b
OLLAMA_EMBED_MODEL=qwen3-embedding:4b

# ChromaDB
CHROMA_DB_PATH=./chroma_db
```

## Project Structure

```
RAG/
├── laravel/                        # Laravel + Inertia.js web application
│   ├── app/
│   │   ├── Http/Controllers/       # Upload, chat, and file management controllers
│   │   ├── Events/                  # Broadcast events (e.g. file status updates)
│   │   └── Jobs/                     # Queued jobs that trigger rag-service
│   ├── resources/
│   │   └── js/
│   │       ├── Components/           # Reusable Vue components (Sidebar, etc.)
│   │       └── Pages/                 # Inertia page components
│   └── routes/
│       └── web.php                    # Application routes
│
└── rag-service/                    # Python service for embedding + answer generation
    ├── chroma_db/                   # Persistent ChromaDB storage (gitignored)
    ├── requirements.txt              # Python dependencies
    └── ...                             # Embedding/query scripts
```

## Usage

1. Navigate to **Documents** and upload a PDF or DOCX file.
2. The file status updates in real time as it moves from **Pending** → **Processing** → **Completed**.
3. During processing, the document is chunked and embedded via `qwen3-embedding:4b` and stored in the ChromaDB `docs` collection.
4. Once completed, go to **AI Chat** and ask questions — relevant chunks are retrieved from ChromaDB and passed to `gemma4:31b` for the response.

## Notes

This is a personal/portfolio project built to explore RAG pipelines, real-time WebSocket updates, and fully self-hosted AI inference without relying on third-party APIs. There's no authentication layer yet. Not currently open to outside contributions.

## License

All rights reserved. No license is granted for reuse, modification, or distribution of this code without explicit permission.