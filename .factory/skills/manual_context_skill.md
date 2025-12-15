# Skill: Manual Context Retrieval

## Deskripsi
Skill ini menjelaskan cara retrieve konteks dari manual book SIWUR untuk RAG chatbot.

## Lokasi File Manual
```
ai_service/general_manuals/siwur_complete_manual.txt
```

## Struktur Manual

Manual SIWUR berisi 10 section utama:

1. **TINJAUAN SISTEM** - Overview sistem SIWUR
2. **PERAN DAN IZIN PENGGUNA** - 4 role: Admin, Kasir, Staff Gudang, Akuntan
3. **FITUR DASHBOARD** - Komponen dan penggunaan dashboard
4. **MANAJEMEN DATA UTAMA** - Satuan, Jenis Barang, Barang, Supplier, Customer, Gudang
5. **FITUR TRANSAKSI** - Pembelian, Penjualan, Retur
6. **FITUR LAPORAN** - Laporan Stok, Pembayaran, Keuntungan
7. **STOCK OPNAME** - Penghitungan inventaris fisik
8. **MANAJEMEN PENGGUNA** - Kelola user dan akses
9. **PINTASAN KEYBOARD** - Shortcut untuk efisiensi
10. **MATRIKS IZIN** - Permission matrix per role

## Chunking Strategy

### Semantic Chunking
Bagi manual berdasarkan section semantik:

```python
def chunk_manual(manual_text: str) -> List[Document]:
    """Chunk manual berdasarkan section"""
    sections = manual_text.split("=" * 80)
    
    chunks = []
    current_section = ""
    
    for section in sections:
        section = section.strip()
        if not section:
            continue
            
        # Detect section header
        lines = section.split("\n")
        if lines[0].strip().startswith(tuple("0123456789")):
            current_section = lines[0].strip()
        
        # Create document with metadata
        doc = Document(
            page_content=section,
            metadata={
                "source": "manual",
                "section": current_section,
                "type": "guide"
            }
        )
        chunks.append(doc)
    
    return chunks
```

### Subsection Chunking
Untuk section yang panjang, chunk lebih granular:

```python
def chunk_by_subsection(section_text: str) -> List[Document]:
    """Chunk section berdasarkan subsection (CARA ...)"""
    subsections = []
    current_chunk = []
    
    for line in section_text.split("\n"):
        if line.startswith("CARA ") or line.startswith("Langkah "):
            if current_chunk:
                subsections.append("\n".join(current_chunk))
            current_chunk = [line]
        else:
            current_chunk.append(line)
    
    if current_chunk:
        subsections.append("\n".join(current_chunk))
    
    return subsections
```

## Embedding dan Vector Store

### Setup ChromaDB untuk Manual
```python
from langchain_community.vectorstores import Chroma
from langchain_google_genai import GoogleGenerativeAIEmbeddings
import chromadb

def create_manual_vectorstore(manual_path: str, persist_dir: str):
    """Buat vector store untuk manual"""
    
    # Load dan chunk manual
    with open(manual_path, "r", encoding="utf-8") as f:
        manual_text = f.read()
    
    chunks = chunk_manual(manual_text)
    
    # Setup embeddings
    embeddings = GoogleGenerativeAIEmbeddings(
        model="models/embedding-001",
        google_api_key=os.getenv("GEMINI_API_KEY")
    )
    
    # Create Chroma vectorstore
    vectorstore = Chroma.from_documents(
        documents=chunks,
        embedding=embeddings,
        collection_name="siwur_manual",
        persist_directory=persist_dir
    )
    
    return vectorstore
```

### Query Manual dengan Similarity Search
```python
def search_manual(query: str, vectorstore, k: int = 3) -> List[Document]:
    """Search manual dengan similarity search"""
    
    results = vectorstore.similarity_search(
        query=query,
        k=k,
        filter={"source": "manual"}
    )
    
    return results
```

## Keyword-Based Fallback

Untuk pertanyaan spesifik, gunakan keyword matching sebagai fallback:

```python
MANUAL_KEYWORDS = {
    "satuan": ["satuan", "unit", "kilogram", "pcs", "konversi"],
    "barang": ["barang", "item", "produk", "kode_barang", "nama_barang"],
    "penjualan": ["penjualan", "jual", "sales", "kasir", "nota"],
    "pembelian": ["pembelian", "beli", "purchase", "supplier", "po"],
    "stok": ["stok", "stock", "gudang", "inventaris", "opname"],
    "laporan": ["laporan", "report", "profit", "keuntungan", "pembayaran"],
    "user": ["user", "pengguna", "role", "admin", "kasir", "akses"],
    "retur": ["retur", "return", "kembalikan", "refund"],
}

def keyword_search(query: str, manual_text: str) -> List[str]:
    """Fallback keyword search"""
    query_lower = query.lower()
    
    # Find matching sections
    matching_sections = []
    
    for section_key, keywords in MANUAL_KEYWORDS.items():
        if any(kw in query_lower for kw in keywords):
            # Extract relevant section from manual
            section = extract_section(manual_text, section_key)
            if section:
                matching_sections.append(section)
    
    return matching_sections
```

## Context Formatting untuk LLM

```python
def format_manual_context(documents: List[Document]) -> str:
    """Format dokumen manual untuk konteks LLM"""
    
    context_parts = []
    
    for i, doc in enumerate(documents, 1):
        section = doc.metadata.get("section", "Unknown")
        content = doc.page_content.strip()
        
        context_parts.append(f"""
=== Panduan SIWUR - {section} ===
{content}
""")
    
    return "\n".join(context_parts)
```

## Hybrid Retrieval: Manual + Database

Untuk pertanyaan yang membutuhkan kedua sumber:

```python
def hybrid_retrieve(question: str, toko_id: int) -> str:
    """Retrieve dari manual dan database"""
    
    # Get manual context
    manual_docs = search_manual(question, manual_vectorstore, k=2)
    manual_context = format_manual_context(manual_docs)
    
    # Get database context
    db_context = get_database_context(question, toko_id)
    
    # Combine contexts
    combined_context = f"""
## Panduan Sistem
{manual_context}

## Data Toko Anda
{db_context}
"""
    
    return combined_context
```

## Pertanyaan-Pertanyaan Manual Umum

### Cara Menggunakan Fitur
- "Bagaimana cara membuat penjualan baru?"
- "Langkah-langkah stock opname"
- "Cara menambah item baru"

### Izin dan Role
- "Apa saja yang bisa dilakukan kasir?"
- "Siapa yang bisa menghapus data?"
- "Akses apa yang dimiliki staff gudang?"

### Shortcut dan Tips
- "Shortcut keyboard untuk penjualan"
- "Cara cepat scan barcode"
- "Tips menggunakan dashboard"

## Caching untuk Performance

```python
import hashlib
from functools import lru_cache

@lru_cache(maxsize=100)
def cached_manual_search(query_hash: str):
    """Cache hasil search manual"""
    # Search implementation
    pass

def search_with_cache(query: str) -> List[Document]:
    """Search dengan caching"""
    query_hash = hashlib.md5(query.encode()).hexdigest()
    return cached_manual_search(query_hash)
```

## Best Practices

1. **Pre-embed manual** - Buat embeddings sekali saat startup
2. **Update index saat manual berubah** - Rebuild index jika manual diupdate
3. **Section metadata** - Simpan metadata section untuk filtering
4. **Combine retrieval methods** - Gunakan semantic + keyword search
5. **Relevance threshold** - Filter dokumen dengan similarity score rendah
