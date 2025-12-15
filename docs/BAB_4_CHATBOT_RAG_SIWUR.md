# BAB 4: IMPLEMENTASI CHATBOT RAG SISTEM SIWUR

## Daftar Isi
1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [User Requirements Specification (URS)](#2-user-requirements-specification-urs)
3. [Software Requirements Specification (SRS)](#3-software-requirements-specification-srs)
4. [Peran Pengguna](#4-peran-pengguna)
5. [Diagram Sistem](#5-diagram-sistem)
6. [Source Code](#6-source-code)
7. [Database](#7-database)
8. [Knowledge Base](#8-knowledge-base)
9. [Frontend Interface](#9-frontend-interface)
10. [API Documentation](#10-api-documentation)
11. [Algoritma](#11-algoritma)
12. [Permodelan Matematika](#12-permodelan-matematika)
13. [Implementasi Detail](#13-implementasi-detail)
14. [Integration & Testing](#14-integration--testing)
15. [Challenges & Solutions](#15-challenges--solutions)

---

## 1. Arsitektur Sistem

### 1.1 Overview Sistem
Sistem chatbot RAG (Retrieval-Augmented Generation) SIWUR dirancang untuk menjawab pertanyaan pengguna dengan menggabungkan:
- **Database Query**: Data real-time dari database MySQL (stok, transaksi, customer)
- **Knowledge Base**: Manual penggunaan sistem dalam format teks
- **Analytics**: Market Basket Analysis untuk rekomendasi produk
- **LLM Generation**: Google Gemini API untuk menghasilkan jawaban natural

### 1.2 Komponen Utama
| Komponen | Teknologi | Fungsi |
|----------|-----------|--------|
| Frontend | Laravel Livewire | Antarmuka chat interaktif |
| Backend API | FastAPI (Python) | Pemrosesan RAG dan AI |
| Database | MySQL | Penyimpanan data transaksional |
| Vector Store | ChromaDB | Penyimpanan embedding dokumen |
| LLM | Google Gemini 2.0 Flash | Generasi jawaban |
| Embeddings | Sentence-Transformers | Konversi teks ke vektor |

---

## 2. User Requirements Specification (URS)

### 2.1 Tabel URS

| ID | Kategori | Kebutuhan Pengguna | Prioritas | Sumber |
|----|----------|-------------------|-----------|--------|
| URS-001 | Factoid | Sistem harus dapat menjawab pertanyaan stok barang secara real-time | High | RAGAS Dataset |
| URS-002 | Factoid | Sistem harus dapat menampilkan informasi toko (nama, alamat) | High | RAGAS Dataset |
| URS-003 | Factoid | Sistem harus dapat menampilkan harga jual produk | High | RAGAS Dataset |
| URS-004 | Factoid | Sistem harus dapat menampilkan informasi supplier | Medium | RAGAS Dataset |
| URS-005 | Factoid | Sistem harus dapat menampilkan informasi gudang | Medium | RAGAS Dataset |
| URS-006 | Factoid | Sistem harus dapat menampilkan informasi customer | Medium | RAGAS Dataset |
| URS-007 | Analytical | Sistem harus dapat menganalisis produk terlaris | High | RAGAS Dataset |
| URS-008 | Analytical | Sistem harus dapat menganalisis trend penjualan | High | RAGAS Dataset |
| URS-009 | Analytical | Sistem harus dapat mengidentifikasi produk slow-moving | Medium | RAGAS Dataset |
| URS-010 | Analytical | Sistem harus dapat menghitung margin keuntungan | High | RAGAS Dataset |
| URS-011 | Comparative | Sistem harus dapat membandingkan performa produk | Medium | RAGAS Dataset |
| URS-012 | Comparative | Sistem harus dapat membandingkan stok antar kategori | Medium | RAGAS Dataset |
| URS-013 | Multi-hop | Sistem harus dapat menghitung estimasi nilai restock | High | RAGAS Dataset |
| URS-014 | Multi-hop | Sistem harus dapat memberikan rekomendasi reorder | High | RAGAS Dataset |
| URS-015 | Recommendation | Sistem harus dapat merekomendasikan produk bundling | High | RAGAS Dataset |
| URS-016 | Recommendation | Sistem harus dapat memberikan saran cross-selling | Medium | RAGAS Dataset |
| URS-017 | Exploratory | Sistem harus dapat menampilkan daftar lengkap master data | Medium | RAGAS Dataset |
| URS-018 | Aggregation | Sistem harus dapat menghitung total dan rata-rata data | High | RAGAS Dataset |
| URS-019 | Temporal | Sistem harus dapat menjawab pertanyaan berbasis waktu | Medium | RAGAS Dataset |
| URS-020 | Guide | Sistem harus dapat menjelaskan cara penggunaan fitur | High | Manual |

### 2.2 Klasifikasi Pertanyaan dari RAGAS Dataset

```
Total Pertanyaan: 51
├── Factoid (Fakta): 10 pertanyaan
├── Analytical (Analisis): 10 pertanyaan
├── Comparative (Perbandingan): 8 pertanyaan
├── Multi-hop (Multi-langkah): 7 pertanyaan
├── Exploratory (Eksplorasi): 5 pertanyaan
├── Aggregation (Agregasi): 5 pertanyaan
├── Temporal (Waktu): 3 pertanyaan
└── Recommendation (Rekomendasi): 3 pertanyaan
```

---

## 3. Software Requirements Specification (SRS)

### 3.1 Functional Requirements

| ID | Requirement | Deskripsi | Input | Output |
|----|------------|-----------|-------|--------|
| FR-001 | Query Routing | Sistem harus dapat mengklasifikasikan pertanyaan ke datasource yang tepat | Pertanyaan pengguna | Kategori: database/manual/analytics/hybrid |
| FR-002 | Database Retrieval | Sistem harus dapat mengambil data dari database berdasarkan konteks pertanyaan | Pertanyaan + toko_id | Dokumen relevan dari DB |
| FR-003 | Manual Retrieval | Sistem harus dapat mencari panduan dari knowledge base | Pertanyaan | Dokumen relevan dari manual |
| FR-004 | Analytics Processing | Sistem harus dapat menjalankan Market Basket Analysis | toko_id + periode | Association rules |
| FR-005 | Answer Generation | Sistem harus dapat menghasilkan jawaban natural dari konteks | Konteks + pertanyaan | Jawaban dalam Bahasa Indonesia |
| FR-006 | Hallucination Check | Sistem harus memvalidasi jawaban berdasarkan fakta | Jawaban + konteks | Valid/Invalid |
| FR-007 | Query Rewriting | Sistem harus dapat menulis ulang pertanyaan untuk retrieval yang lebih baik | Pertanyaan awal | Pertanyaan yang dioptimasi |
| FR-008 | Multi-tenant Support | Sistem harus mendukung isolasi data per toko | toko_id | Data terisolasi |

### 3.2 Non-Functional Requirements

| ID | Kategori | Requirement | Target |
|----|----------|-------------|--------|
| NFR-001 | Performance | Response time chat | < 5 detik |
| NFR-002 | Performance | Concurrent users | 100 users |
| NFR-003 | Availability | Uptime | 99.5% |
| NFR-004 | Scalability | Horizontal scaling | Supported |
| NFR-005 | Security | Data isolation | Per toko_id |
| NFR-006 | Reliability | Fallback mechanism | 3 level fallback |
| NFR-007 | Maintainability | Index rebuild | Tanpa downtime |

---

## 4. Peran Pengguna

### 4.1 Pemilik Toko (Admin)

**Deskripsi**: Pemilik toko memiliki akses penuh ke seluruh fitur sistem termasuk chatbot. Dapat mengakses semua jenis pertanyaan dan laporan.

**Pertanyaan Chatbot yang Relevan**:
1. "Berapa total omzet bulan ini?"
2. "Produk apa yang paling laku?"
3. "Bagaimana trend penjualan 3 bulan terakhir?"
4. "Supplier mana yang paling sering diorder?"
5. "Berapa margin keuntungan rata-rata?"
6. "Customer mana yang cocok untuk program grosir?"
7. "Produk apa yang cocok untuk bundling?"

**Akses Data**:
- Semua master data (barang, supplier, customer, gudang)
- Semua transaksi (pembelian, penjualan, retur)
- Semua laporan (stok, profit, pembayaran)
- Analytics dan rekomendasi

### 4.2 Akuntan

**Deskripsi**: Akuntan fokus pada aspek keuangan dan pelaporan. Memiliki akses read-only ke data transaksi dan laporan keuangan.

**Pertanyaan Chatbot yang Relevan**:
1. "Berapa total penjualan bulan November?"
2. "Berapa piutang yang belum dibayar?"
3. "Bagaimana perbandingan pembelian vs penjualan?"
4. "Transaksi mana yang belum lunas?"
5. "Berapa rata-rata nilai transaksi per hari?"

**Akses Data**:
- Laporan pembayaran
- Laporan profit
- View transaksi pembelian/penjualan
- Tidak dapat mengubah data master

### 4.3 Staff Gudang

**Deskripsi**: Staff gudang bertanggung jawab atas inventaris dan pergerakan stok. Fokus pada pertanyaan terkait stok dan penerimaan barang.

**Pertanyaan Chatbot yang Relevan**:
1. "Berapa stok Indomie Goreng saat ini?"
2. "Produk apa yang stoknya menipis?"
3. "Barang apa yang perlu di-restock minggu depan?"
4. "Di gudang mana stok Aqua paling banyak?"
5. "Kapan terakhir melakukan stock opname?"
6. "Berapa total item di Gudang Utama?"

**Akses Data**:
- Stok gudang (view & edit)
- Stock opname
- View pembelian (verifikasi barang masuk)
- Tidak dapat akses penjualan dan laporan keuangan

---

## 5. Diagram Sistem

### 5.1 System Architecture & Component Diagram

```plantuml
@startuml System_Architecture_Component
!theme plain
skinparam backgroundColor #FEFEFE
skinparam componentStyle rectangle

title System Architecture & Component Diagram - SIWUR RAG Chatbot

package "Frontend (Laravel Livewire)" #LightBlue {
    component "chatbot-component.blade.php" as VIEW {
        [Chat UI]
        [Alpine.js State]
    }
    component "ChatbotComponent.php" as COMP {
        [Message Handler]
        [Session Manager]
    }
    component "ChatbotController.php" as CTRL {
        [Request Validator]
        [Toko ID Resolver]
        [HTTP Client]
    }
}

package "Backend API (Python FastAPI)" #LightGreen {
    component "app.py" as APP {
        [/chat endpoint]
        [/health endpoint]
        [/mba endpoint]
    }
    
    package "core" #LightYellow {
        component "adaptive_rag.py" as RAG {
            [Query Router]
            [Document Grader]
            [Answer Generator]
            [Hallucination Checker]
        }
        component "embeddings.py" as EMBED
        component "database.py" as DBCORE
        component "config.py" as CONFIG
    }
    
    package "skills" #LightCoral {
        component "database_skill.py" as DBSKILL
        component "manual_skill.py" as MANSKILL
        component "analytics_skill.py" as ANSKILL
    }
}

package "Data Layer" #LightGray {
    database "MySQL" as MYSQL {
        [toko]
        [barang]
        [penjualan]
        [chat_messages]
    }
    database "ChromaDB" as CHROMA {
        [Vector Index]
        [Embeddings]
    }
}

cloud "External API" #White {
    [Google Gemini API] as GEMINI
}

' Frontend connections
VIEW --> COMP : wire:submit
COMP --> CTRL : HTTP Request
CTRL --> APP : POST /chat

' Backend internal connections
APP --> RAG : process question
RAG --> DBSKILL : database queries
RAG --> MANSKILL : manual search
RAG --> ANSKILL : analytics
DBSKILL --> DBCORE : execute SQL
MANSKILL --> EMBED : similarity search
ANSKILL --> DBCORE : MBA queries
RAG --> GEMINI : generate answer

' Data layer connections
DBCORE --> MYSQL : SQLAlchemy
EMBED --> CHROMA : langchain-chroma
CTRL --> MYSQL : chat_messages

@enduml
```

### 5.2 Database ERD (Tabel Chatbot)

```plantuml
@startuml ERD_Chatbot
!theme plain
skinparam backgroundColor #FEFEFE
skinparam linetype ortho

title Entity Relationship Diagram - Tabel yang Digunakan Chatbot

entity "users" as USERS {
    * id : bigint <<PK>>
    --
    name : varchar(255)
    email : varchar(255)
    password : varchar(255)
    created_at : timestamp
    updated_at : timestamp
}

entity "toko" as TOKO {
    * id : bigint <<PK>>
    --
    nama_toko : varchar(255)
    logo_toko : varchar(255) <<nullable>>
    alamat_toko : varchar(255) <<nullable>>
    user_id : bigint <<FK>>
    created_at : timestamp
    updated_at : timestamp
}

entity "jenis_barang" as JENIS {
    * id : bigint <<PK>>
    --
    nama_jenis_barang : varchar(255)
    keterangan : varchar(255) <<nullable>>
    toko_id : bigint <<FK>>
    created_at : timestamp
    updated_at : timestamp
}

entity "satuan" as SATUAN {
    * id : bigint <<PK>>
    --
    nama_satuan : varchar(255)
    keterangan : varchar(255) <<nullable>>
    toko_id : bigint <<FK>>
    created_at : timestamp
    updated_at : timestamp
}

entity "barang" as BARANG {
    * id : bigint <<PK>>
    --
    kode_barang : varchar(255)
    nama_barang : varchar(255)
    keterangan : varchar(255) <<nullable>>
    jenis_barang_id : bigint <<FK>>
    satuan_terkecil_id : bigint <<FK>>
    toko_id : bigint <<FK>>
    created_at : timestamp
    updated_at : timestamp
    ..
    <<unique>> (kode_barang, toko_id)
}

entity "gudang" as GUDANG {
    * id : bigint <<PK>>
    --
    nama_gudang : varchar(255)
    keterangan : varchar(255) <<nullable>>
    toko_id : bigint <<FK>>
    created_at : timestamp
    updated_at : timestamp
}

entity "gudang_stock" as STOCK {
    * id : bigint <<PK>>
    --
    gudang_id : bigint <<FK>>
    barang_id : bigint <<FK>>
    jumlah : decimal(10,2)
    created_at : timestamp
    updated_at : timestamp
    ..
    <<unique>> (gudang_id, barang_id)
}

entity "customer" as CUSTOMER {
    * id : bigint <<PK>>
    --
    nama_customer : varchar(255)
    alamat : varchar(255) <<nullable>>
    no_hp : varchar(255) <<nullable>>
    email : varchar(255) <<nullable>>
    keterangan : varchar(255) <<nullable>>
    is_opname : boolean = false
    toko_id : bigint <<FK>>
    created_at : timestamp
    updated_at : timestamp
}

entity "supplier" as SUPPLIER {
    * id : bigint <<PK>>
    --
    nama_supplier : varchar(255)
    alamat : varchar(255) <<nullable>>
    no_hp : varchar(255) <<nullable>>
    email : varchar(255) <<nullable>>
    keterangan : varchar(255) <<nullable>>
    is_opname : boolean = false
    toko_id : bigint <<FK>>
    created_at : timestamp
    updated_at : timestamp
}

entity "penjualan" as PENJUALAN {
    * id : bigint <<PK>>
    --
    nomor_penjualan : varchar(255) <<unique>>
    tanggal_penjualan : datetime
    customer_id : bigint <<FK>>
    user_id : bigint <<FK>>
    keterangan : varchar(255) <<nullable>>
    total_harga : decimal(10,2)
    status : enum('belum_bayar','belum_lunas','lunas')
    toko_id : bigint <<FK>>
    kembalian : decimal(10,2) <<nullable>>
    created_at : timestamp
    updated_at : timestamp
}

entity "penjualan_detail" as PENJUALAN_DET {
    * id : bigint <<PK>>
    --
    penjualan_id : bigint <<FK>>
    pembelian_detail_id : bigint <<FK>>
    barang_id : bigint <<FK>>
    satuan_id : bigint <<FK>>
    gudang_id : bigint <<FK>>
    harga_satuan : decimal(15,2)
    diskon : decimal(15,2) = 0
    biaya_lain : decimal(15,2) = 0
    jumlah : decimal(15,2)
    konversi_satuan_terkecil : decimal(15,2) = 0
    subtotal : decimal(15,2)
    profit : decimal(15,2)
    created_at : timestamp
    updated_at : timestamp
}

entity "chat_messages" as CHAT {
    * id : bigint <<PK>>
    --
    session_id : varchar(255) <<index>>
    toko_id : bigint <<FK>>
    role : enum('user','bot')
    content : text
    created_at : timestamp
    updated_at : timestamp
}

' Relationships
USERS ||--o{ TOKO : owns
TOKO ||--o{ JENIS : has
TOKO ||--o{ SATUAN : has
TOKO ||--o{ BARANG : has
TOKO ||--o{ GUDANG : has
TOKO ||--o{ CUSTOMER : has
TOKO ||--o{ SUPPLIER : has
TOKO ||--o{ PENJUALAN : has
TOKO ||--o{ CHAT : has

JENIS ||--o{ BARANG : categorizes
SATUAN ||--o{ BARANG : measures

GUDANG ||--o{ STOCK : contains
BARANG ||--o{ STOCK : stored_in

CUSTOMER ||--o{ PENJUALAN : makes
USERS ||--o{ PENJUALAN : processes
PENJUALAN ||--o{ PENJUALAN_DET : contains

BARANG ||--o{ PENJUALAN_DET : sold_in
SATUAN ||--o{ PENJUALAN_DET : unit_of
GUDANG ||--o{ PENJUALAN_DET : from

@enduml
```

### 5.3 System Flow Diagram (3 Layer)

```plantuml
@startuml System_Flow_3Layer
!theme plain
skinparam backgroundColor #FEFEFE

title RAG Chatbot System Flow Diagram

|#LightBlue|Frontend (Laravel)|
start
:User mengetik pertanyaan;
:Klik tombol Kirim;
:Tampilkan pesan user di chat;
:Tampilkan typing indicator;
:Livewire dispatch event;
:ChatbotComponent memanggil Controller;
:ChatbotController validasi input;
:Resolve toko_id dari session/user;
:Simpan pesan user ke chat_messages;
:Kirim HTTP POST ke AI Service;

|#LightGreen|Backend (Laravel)|
:Terima request dari Controller;
:HTTP Client timeout 60 detik;

|#LightYellow|AI Service (Python)|
:FastAPI terima POST /chat;
:Parse request (question, tenant_id);
:=== QUERY ROUTING ===;
:Analisis keywords pertanyaan;
if (Tipe pertanyaan?) then (database)
    :datasource = "database";
    :Jalankan DatabaseSkill;
    :Query MySQL (stock, sales, etc);
elseif (manual) then (manual)
    :datasource = "manual";
    :Jalankan ManualSkill;
    :Semantic search di ChromaDB;
elseif (analytics) then (analytics)
    :datasource = "analytics";
    :Jalankan AnalyticsSkill;
    :Hitung Market Basket Analysis;
else (hybrid)
    :datasource = "hybrid";
    :Jalankan semua skills;
endif
:=== DOCUMENT GRADING ===;
:Filter dokumen relevan dengan LLM;
if (Ada dokumen relevan?) then (tidak)
    :Query rewriting;
    :Retry retrieval;
endif
:Format context dari dokumen;
:=== ANSWER GENERATION ===;
:Generate jawaban dengan Gemini;
:=== HALLUCINATION CHECK ===;
:Validasi jawaban vs context;
if (Hallucination terdeteksi?) then (ya)
    :Regenerate jawaban;
endif
:Return JSON response;

|#LightGreen|Backend (Laravel)|
:Terima response dari AI Service;
:Parse JSON response;

|#LightBlue|Frontend (Laravel)|
:Terima jawaban dari Controller;
:Simpan jawaban ke chat_messages;
:Sembunyikan typing indicator;
:Tampilkan jawaban bot di chat;
:Auto-scroll ke pesan terbaru;
:User membaca jawaban;
stop

@enduml
```

### 5.4 Layered Architecture Diagram

```plantuml
@startuml Layered_Architecture
!theme plain
skinparam backgroundColor #FEFEFE

title Layered Architecture - SIWUR RAG System

package "Presentation Layer" #LightBlue {
    [Livewire Components]
    [Blade Templates]
    [Alpine.js]
}

package "Application Layer" #LightGreen {
    [ChatbotController]
    [FastAPI Endpoints]
    [Request Validation]
}

package "Business Logic Layer" #LightYellow {
    [Adaptive RAG Pipeline]
    [Query Router]
    [Skills (Database, Manual, Analytics)]
    [Hallucination Checker]
}

package "Data Access Layer" #LightCoral {
    [SQLAlchemy ORM]
    [ChromaDB Client]
    [Query Templates]
}

package "Infrastructure Layer" #LightGray {
    [MySQL Database]
    [ChromaDB Vector Store]
    [Google Gemini API]
    [Sentence Transformers]
}

[Livewire Components] --> [ChatbotController]
[ChatbotController] --> [FastAPI Endpoints]
[FastAPI Endpoints] --> [Adaptive RAG Pipeline]
[Adaptive RAG Pipeline] --> [Query Router]
[Query Router] --> [Skills (Database, Manual, Analytics)]
[Skills (Database, Manual, Analytics)] --> [SQLAlchemy ORM]
[Skills (Database, Manual, Analytics)] --> [ChromaDB Client]
[SQLAlchemy ORM] --> [MySQL Database]
[ChromaDB Client] --> [ChromaDB Vector Store]
[Adaptive RAG Pipeline] --> [Google Gemini API]

@enduml
```

---

## 6. Source Code

### 6.1 Core Module - adaptive_rag.py

```python
"""
Adaptive RAG Pipeline Implementation
File: ai_service/core/adaptive_rag.py
"""

import google.generativeai as genai
from typing import List, Dict, Any, Optional, Literal
from pydantic import BaseModel, Field
from langchain_core.documents import Document

class RouteQuery(BaseModel):
    """Route query ke datasource yang tepat"""
    datasource: Literal["database", "manual", "analytics", "hybrid"] = Field(
        description="Pilih datasource: database untuk data real-time, manual untuk panduan, analytics untuk analisis, hybrid untuk kombinasi"
    )
    reasoning: str = Field(description="Alasan pemilihan datasource")

class GradeDocuments(BaseModel):
    """Skor biner untuk relevansi dokumen"""
    binary_score: Literal["yes", "no"] = Field(
        description="Dokumen relevan dengan pertanyaan: 'yes' atau 'no'"
    )

class AdaptiveRAG:
    """Adaptive RAG implementation with Gemini"""
    
    def __init__(self):
        self.model = genai.GenerativeModel(settings.gemini_model)
        self.database_skill = DatabaseSkill()
        self.manual_skill = ManualSkill()
        self.analytics_skill = AnalyticsSkill()
        self.max_retries = settings.max_retries
    
    def route_question(self, question: str) -> RouteQuery:
        """Route pertanyaan ke datasource yang tepat"""
        prompt = f"""Anda adalah router yang menentukan datasource untuk menjawab pertanyaan tentang sistem SIWUR.

Datasource yang tersedia:
1. **database** - Data real-time toko: stok barang, transaksi, customer, supplier
2. **manual** - Panduan penggunaan sistem: cara menggunakan fitur, alur kerja
3. **analytics** - Analisis data: Market Basket Analysis, produk terlaris, trend
4. **hybrid** - Kombinasi database dan manual

Pertanyaan: {question}
"""
        response = self.model.generate_content(prompt)
        return RouteQuery.model_validate_json(response.text)
    
    def retrieve(self, state: GraphState) -> List[Document]:
        """Retrieve documents based on datasource"""
        documents = []
        
        if state.datasource in ["database", "hybrid"]:
            db_docs = self.database_skill.retrieve(state.question, state.toko_id)
            documents.extend(db_docs)
        
        if state.datasource in ["manual", "hybrid"]:
            manual_docs = self.manual_skill.retrieve(state.question, state.toko_id)
            documents.extend(manual_docs)
        
        if state.datasource in ["analytics", "hybrid"]:
            analytics_docs = self.analytics_skill.retrieve(state.question, state.toko_id)
            documents.extend(analytics_docs)
        
        return documents
    
    def run(self, question: str, toko_id: int) -> Dict[str, Any]:
        """Run the complete adaptive RAG pipeline"""
        state = GraphState(question=question, toko_id=toko_id)
        
        # Step 1: Route question
        route = self.route_question(question)
        state.datasource = route.datasource
        
        # Step 2: Retrieve documents
        state.documents = self.retrieve(state)
        
        # Step 3: Grade and filter documents
        filtered_docs = self._grade_documents(state.question, state.documents)
        
        # Step 4: Generate answer
        context = self._format_context(filtered_docs)
        state.generation = self.generate_answer(state.question, context)
        
        # Step 5: Validate answer
        if not self._check_hallucination(context, state.generation):
            state.generation = self.generate_answer(state.question, context)
        
        return {
            "question": question,
            "answer": state.generation,
            "datasource": state.datasource,
            "documents_count": len(filtered_docs),
        }
```

### 6.2 Database Skill - database_skill.py

```python
"""
Database Skill for RAG Context Retrieval
File: ai_service/skills/database_skill.py
"""

from typing import List, Dict, Any
from langchain_core.documents import Document
from skills.base import BaseSkill
from core.database import execute_query, QUERY_TEMPLATES

class DatabaseSkill(BaseSkill):
    """Skill for retrieving context from database"""
    
    name = "database"
    description = "Retrieve real-time data from database"
    keywords = [
        "stok", "stock", "barang", "produk", "harga",
        "supplier", "customer", "penjualan", "pembelian",
        "transaksi", "gudang", "kategori", "berapa"
    ]
    
    QUERY_MAPPING = {
        "stok": ["stock_info", "low_stock"],
        "supplier": ["suppliers"],
        "customer": ["customers"],
        "kategori": ["categories"],
        "penjualan": ["recent_sales", "top_selling_products"],
        "profit": ["profit_summary"],
    }
    
    def retrieve(self, question: str, toko_id: int, **kwargs) -> List[Document]:
        """Retrieve data from database based on question"""
        query_types = self._detect_query_types(question)
        documents = []
        
        for query_type in query_types:
            results = execute_query(QUERY_TEMPLATES[query_type], {"toko_id": toko_id})
            if results:
                doc = Document(
                    page_content=self._format_results(query_type, results),
                    metadata={"source": "database", "query_type": query_type}
                )
                documents.append(doc)
        
        return documents
```

### 6.3 Analytics Skill - analytics_skill.py

```python
"""
Analytics Skill with Market Basket Analysis
File: ai_service/skills/analytics_skill.py
"""

from typing import List, Dict, Any
from langchain_core.documents import Document
from mlxtend.frequent_patterns import apriori, association_rules

class AnalyticsSkill(BaseSkill):
    """Skill for analytics including Market Basket Analysis"""
    
    def run_full_mba(self, toko_id: int) -> Dict[str, Any]:
        """Run full Market Basket Analysis with Apriori algorithm"""
        
        # Get transaction data
        df = execute_query_df(QUERY_TEMPLATES["transaction_baskets"], 
                             {"toko_id": toko_id, "days": 90})
        
        # Group items by transaction (basket format)
        baskets = df.groupby('transaction_id')['item'].apply(list).tolist()
        
        # One-hot encode
        te = TransactionEncoder()
        basket_df = pd.DataFrame(te.fit_transform(baskets), columns=te.columns_)
        
        # Run Apriori algorithm
        frequent_itemsets = apriori(basket_df, min_support=0.01, use_colnames=True)
        
        # Generate association rules
        rules = association_rules(frequent_itemsets, metric="confidence", 
                                 min_threshold=0.3)
        
        return {
            "total_transactions": len(baskets),
            "rules_found": len(rules),
            "top_rules": self._format_rules(rules.head(10))
        }
```

### 6.4 FastAPI Application - app.py

```python
"""
FastAPI Application for RAG Chatbot
File: ai_service/app.py
"""

from fastapi import FastAPI, HTTPException, Depends
from pydantic import BaseModel, Field
from typing import Optional

app = FastAPI(
    title="SIWUR AI Chatbot API",
    description="RAG-based chatbot for SIWUR",
    version="1.0.0",
)

class ChatRequest(BaseModel):
    question: str = Field(..., min_length=1, max_length=1000)
    toko_id: Optional[int] = Field(None)
    tenant_id: Optional[str] = Field(None)  # Alias for toko_id
    
    def get_toko_id(self) -> int:
        if self.toko_id: return self.toko_id
        if self.tenant_id: return int(self.tenant_id)
        return 1

class ChatResponse(BaseModel):
    success: bool
    answer: str
    datasource: str
    documents_count: int
    processing_time_ms: int

@app.post("/chat", response_model=ChatResponse)
async def chat(request: ChatRequest, rag: AdaptiveRAG = Depends(get_rag)):
    """Main chat endpoint"""
    start_time = datetime.now()
    
    result = rag.run(
        question=request.question,
        toko_id=request.get_toko_id()
    )
    
    processing_time = int((datetime.now() - start_time).total_seconds() * 1000)
    
    return ChatResponse(
        success=True,
        answer=result["answer"],
        datasource=result["datasource"],
        documents_count=result["documents_count"],
        processing_time_ms=processing_time,
    )

@app.post("/mba")
async def market_basket_analysis(request: MBARequest):
    """Run Market Basket Analysis"""
    analytics = AnalyticsSkill()
    return analytics.run_full_mba(request.toko_id)
```

### 6.5 Laravel Controller - ChatbotController.php

```php
<?php
/**
 * Chatbot Controller
 * File: app/Http/Controllers/ChatbotController.php
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:2048',
            'session_id' => 'required|string',
        ]);

        $user = auth()->user();
        $tokoId = $this->resolveTokoId($user, $request);

        // Save user message
        ChatMessage::create([
            'session_id' => $request->session_id,
            'toko_id' => $tokoId,
            'role' => 'user',
            'content' => $request->question,
        ]);

        // Call AI service
        $response = Http::timeout(60)->post(
            config('services.ai_service.url') . '/chat',
            [
                'question' => $request->question,
                'tenant_id' => (string) $tokoId,
            ]
        );

        $answer = $response->json()['answer'] ?? 'Maaf, terjadi kesalahan.';

        // Save bot response
        ChatMessage::create([
            'session_id' => $request->session_id,
            'toko_id' => $tokoId,
            'role' => 'bot',
            'content' => $answer,
        ]);

        return response()->json(['answer' => $answer]);
    }
}
```

### 6.6 Livewire Component - ChatbotComponent.php

```php
<?php
/**
 * Chatbot Livewire Component
 * File: app/Livewire/ChatbotComponent.php
 */

namespace App\Livewire;

use Livewire\Component;
use App\Http\Controllers\ChatbotController;

class ChatbotComponent extends Component
{
    public array $messages = [];
    public string $question = '';
    public string $sessionId;
    public bool $isBotTyping = false;

    public function mount()
    {
        $this->sessionId = session('chatbot_session_id', (string) Str::uuid());
        $this->messages = session('chatbot_messages', []);
        
        if (empty($this->messages)) {
            $this->messages[] = [
                'role' => 'bot',
                'content' => 'Halo! Saya asisten AI Anda. Ada yang bisa saya bantu?',
                'time' => now()->format('H:i')
            ];
        }
    }

    public function sendMessage()
    {
        if (empty($this->question)) return;

        $this->messages[] = [
            'role' => 'user',
            'content' => $this->question,
            'time' => now()->format('H:i')
        ];

        $this->isBotTyping = true;
        $this->dispatch('get-bot-answer', question: $this->question);
        $this->reset('question');
    }

    #[On('get-bot-answer')]
    public function getBotAnswer(string $question)
    {
        $controller = new ChatbotController();
        $response = $controller->ask(new Request([
            'question' => $question,
            'session_id' => $this->sessionId,
        ]));

        $this->messages[] = [
            'role' => 'bot',
            'content' => $response->json()['answer'],
            'time' => now()->format('H:i')
        ];
        
        $this->isBotTyping = false;
    }
}
```

---

## 7. Database

### 7.1 DDL (Data Definition Language)

```sql
-- =====================================================
-- DDL untuk Sistem SIWUR RAG Chatbot
-- =====================================================

-- Tabel Toko (Multi-tenant)
CREATE TABLE toko (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_toko VARCHAR(255) NOT NULL,
    logo_toko VARCHAR(255) NULL,
    alamat_toko VARCHAR(255) NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Jenis Barang
CREATE TABLE jenis_barang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_jenis_barang VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (toko_id) REFERENCES toko(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Satuan
CREATE TABLE satuan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_satuan VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (toko_id) REFERENCES toko(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Barang
CREATE TABLE barang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(50) NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255) NULL,
    jenis_barang_id BIGINT UNSIGNED NOT NULL,
    satuan_terkecil_id BIGINT UNSIGNED NOT NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_kode_toko (kode_barang, toko_id),
    FOREIGN KEY (jenis_barang_id) REFERENCES jenis_barang(id),
    FOREIGN KEY (satuan_terkecil_id) REFERENCES satuan(id),
    FOREIGN KEY (toko_id) REFERENCES toko(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Gudang
CREATE TABLE gudang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_gudang VARCHAR(255) NOT NULL,
    alamat VARCHAR(255) NULL,
    keterangan VARCHAR(255) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (toko_id) REFERENCES toko(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Gudang Stock
CREATE TABLE gudang_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gudang_id BIGINT UNSIGNED NOT NULL,
    barang_id BIGINT UNSIGNED NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_gudang_barang (gudang_id, barang_id),
    FOREIGN KEY (gudang_id) REFERENCES gudang(id),
    FOREIGN KEY (barang_id) REFERENCES barang(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Customer
CREATE TABLE customer (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_customer VARCHAR(255) NOT NULL,
    alamat VARCHAR(255) NULL,
    telepon VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    keterangan VARCHAR(255) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (toko_id) REFERENCES toko(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Supplier
CREATE TABLE supplier (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_supplier VARCHAR(255) NOT NULL,
    alamat VARCHAR(255) NULL,
    telepon VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    keterangan VARCHAR(255) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (toko_id) REFERENCES toko(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Penjualan
CREATE TABLE penjualan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_penjualan VARCHAR(50) NOT NULL UNIQUE,
    tanggal_penjualan DATETIME NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    keterangan VARCHAR(255) NULL,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('belum_bayar', 'belum_lunas', 'lunas') DEFAULT 'belum_bayar',
    kembalian DECIMAL(10,2) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customer(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (toko_id) REFERENCES toko(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Penjualan Detail
CREATE TABLE penjualan_detail (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    penjualan_id BIGINT UNSIGNED NOT NULL,
    pembelian_detail_id BIGINT UNSIGNED NOT NULL,
    barang_id BIGINT UNSIGNED NOT NULL,
    satuan_id BIGINT UNSIGNED NOT NULL,
    gudang_id BIGINT UNSIGNED NOT NULL,
    harga_satuan DECIMAL(15,2) NOT NULL,
    diskon DECIMAL(15,2) DEFAULT 0,
    biaya_lain DECIMAL(15,2) DEFAULT 0,
    jumlah DECIMAL(15,2) NOT NULL,
    konversi_satuan_terkecil DECIMAL(15,2) DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL,
    profit DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (penjualan_id) REFERENCES penjualan(id),
    FOREIGN KEY (barang_id) REFERENCES barang(id),
    FOREIGN KEY (satuan_id) REFERENCES satuan(id),
    FOREIGN KEY (gudang_id) REFERENCES gudang(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Chat Messages
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    role ENUM('user', 'bot', 'system') NOT NULL,
    content TEXT NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_session_toko (session_id, toko_id),
    FOREIGN KEY (toko_id) REFERENCES toko(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 7.2 Sample Data (INSERT)

```sql
-- =====================================================
-- Sample Data untuk Testing RAG Chatbot
-- =====================================================

-- Insert Toko
INSERT INTO toko (id, nama_toko, alamat_toko, user_id) VALUES
(1, 'Toko Sinar Abadi', 'Jl. Raya Pasar Minggu No. 45, Jakarta Selatan', 1);

-- Insert Jenis Barang
INSERT INTO jenis_barang (id, nama_jenis_barang, toko_id) VALUES
(1, 'Mie & Pasta', 1),
(2, 'Minuman', 1),
(3, 'Beras & Biji-bijian', 1),
(4, 'Minyak & Margarin', 1),
(5, 'Rokok & Korek', 1),
(6, 'Sabun & Deterjen', 1);

-- Insert Satuan
INSERT INTO satuan (id, nama_satuan, toko_id) VALUES
(1, 'Pcs', 1),
(2, 'Pack', 1),
(3, 'Kg', 1),
(4, 'Liter', 1),
(5, 'Karton', 1);

-- Insert Barang
INSERT INTO barang (id, kode_barang, nama_barang, jenis_barang_id, satuan_terkecil_id, toko_id) VALUES
(1, 'MIE001', 'Indomie Goreng Original', 1, 1, 1),
(2, 'MIE002', 'Indomie Kuah Soto', 1, 1, 1),
(3, 'MIE003', 'Mie Sedaap Goreng', 1, 1, 1),
(4, 'MNM001', 'Aqua Botol 600ml', 2, 1, 1),
(5, 'MNM002', 'Teh Botol Sosro', 2, 1, 1),
(6, 'BRS001', 'Beras Premium 5kg', 3, 3, 1),
(7, 'MNY001', 'Minyak Goreng Bimoli 2L', 4, 4, 1),
(8, 'RKK001', 'Rokok Gudang Garam Filter', 5, 2, 1),
(9, 'SBN001', 'Sabun Lifebuoy 75g', 6, 1, 1);

-- Insert Gudang
INSERT INTO gudang (id, nama_gudang, alamat, toko_id) VALUES
(1, 'Gudang Utama', 'Lantai 1', 1),
(2, 'Gudang Dingin', 'Lantai Basement', 1);

-- Insert Gudang Stock
INSERT INTO gudang_stock (gudang_id, barang_id, jumlah) VALUES
(1, 1, 500),  -- Indomie Goreng: 500 pcs
(1, 2, 300),  -- Indomie Kuah: 300 pcs
(1, 3, 200),  -- Mie Sedaap: 200 pcs
(1, 4, 480),  -- Aqua: 480 pcs
(1, 5, 240),  -- Teh Botol: 240 pcs
(1, 6, 150),  -- Beras: 150 kg
(1, 7, 200),  -- Minyak Goreng: 200 liter
(1, 8, 200),  -- Rokok GG: 200 pack
(1, 9, 200);  -- Sabun: 200 pcs

-- Insert Customer
INSERT INTO customer (id, nama_customer, alamat, telepon, toko_id) VALUES
(1, 'Customer Umum', 'Walk-in Customer', '-', 1),
(2, 'Warung Makan Bu Tini', 'Jl. Raya Pasar Minggu No. 12', '08123456789', 1),
(3, 'Toko Kelontong Pak Ahmad', 'Jl. Pejaten Raya No. 5', '08198765432', 1),
(4, 'Catering Berkah Ibu', 'Jl. Kemang Selatan No. 8', '08111222333', 1);

-- Insert Supplier
INSERT INTO supplier (id, nama_supplier, alamat, telepon, toko_id) VALUES
(1, 'PT Indofood CBP Sukses Makmur', 'Jl. Sudirman', '0211234567', 1),
(2, 'PT Unilever Indonesia', 'Jl. Gatot Subroto', '0217654321', 1),
(3, 'CV Beras Sejahtera', 'Jl. Pasar Induk', '08128765432', 1);

-- Insert Sample Penjualan
INSERT INTO penjualan (id, nomor_penjualan, tanggal_penjualan, customer_id, user_id, total_harga, status, toko_id) VALUES
(1, 'PJ-20251201-001', '2025-12-01 09:00:00', 2, 1, 150000, 'lunas', 1),
(2, 'PJ-20251201-002', '2025-12-01 10:30:00', 3, 1, 500000, 'lunas', 1),
(3, 'PJ-20251201-003', '2025-12-01 14:00:00', 1, 1, 75000, 'lunas', 1);

-- Insert Penjualan Detail (untuk MBA)
INSERT INTO penjualan_detail (penjualan_id, pembelian_detail_id, barang_id, satuan_id, gudang_id, harga_satuan, jumlah, subtotal, profit) VALUES
-- Transaksi 1: Indomie + Aqua (sering dibeli bersamaan)
(1, 1, 1, 1, 1, 3500, 20, 70000, 10000),
(1, 1, 4, 1, 1, 4000, 20, 80000, 12000),
-- Transaksi 2: Beras + Minyak + Indomie
(2, 1, 6, 3, 1, 65000, 5, 325000, 50000),
(2, 1, 7, 4, 1, 35000, 5, 175000, 25000),
-- Transaksi 3: Rokok + Aqua
(3, 1, 8, 2, 1, 25000, 2, 50000, 8000),
(3, 1, 4, 1, 1, 4000, 6, 24000, 4000);
```

### 7.3 Database Design Documentation

**Prinsip Desain:**

1. **Multi-tenant Architecture**: Setiap tabel memiliki `toko_id` sebagai foreign key untuk isolasi data antar toko.

2. **FIFO Tracking**: `penjualan_detail` memiliki `pembelian_detail_id` untuk tracking FIFO (First In First Out) dan perhitungan profit.

3. **Denormalisasi Terkontrol**: `total_harga` dan `profit` disimpan langsung untuk performa query analytics.

4. **Indexing Strategy**:
   - Primary key menggunakan `BIGINT UNSIGNED AUTO_INCREMENT`
   - Composite index pada `(toko_id, created_at)` untuk filtering temporal
   - Unique constraint pada `(kode_barang, toko_id)` untuk mencegah duplikasi

---

## 8. Knowledge Base

### 8.1 Desain Knowledge Base

Knowledge Base SIWUR terdiri dari dua sumber utama:

1. **Static Knowledge**: Manual penggunaan sistem (siwur_complete_manual.txt)
2. **Dynamic Knowledge**: Data real-time dari database MySQL

**Struktur Manual:**
```
siwur_complete_manual.txt
├── 1. TINJAUAN SISTEM
├── 2. PERAN DAN IZIN PENGGUNA
├── 3. FITUR DASHBOARD
├── 4. MANAJEMEN DATA UTAMA
│   ├── 4.1 Satuan
│   ├── 4.2 Jenis Barang
│   ├── 4.3 Barang
│   ├── 4.4 Supplier
│   ├── 4.5 Customer
│   └── 4.6 Gudang
├── 5. FITUR TRANSAKSI
│   ├── 5.1 Pembelian
│   ├── 5.2 Penjualan
│   ├── 5.3 Retur Pembelian
│   └── 5.4 Retur Penjualan
├── 6. FITUR LAPORAN
├── 7. STOCK OPNAME
├── 8. MANAJEMEN PENGGUNA
├── 9. PINTASAN KEYBOARD
└── 10. MATRIKS IZIN
```

### 8.2 Sample Query-Answer Pairs

#### Pertanyaan untuk Pemilik Toko (7 pairs)

| No | Pertanyaan | Jawaban | Kategori |
|----|-----------|---------|----------|
| 1 | "Berapa total omzet bulan ini?" | "Total omzet bulan ini adalah Rp 15.750.000 dari 200 transaksi dengan rata-rata Rp 78.750 per transaksi." | Aggregation |
| 2 | "Produk apa yang paling laku?" | "Produk terlaris adalah: 1) Indomie Goreng Original (500 pcs), 2) Rokok Gudang Garam (350 pack), 3) Aqua Botol 600ml (480 pcs)" | Analytical |
| 3 | "Supplier mana yang paling sering diorder?" | "PT Indofood CBP paling sering diorder (15 PO/bulan), diikuti PT Unilever Indonesia (10 PO/bulan)" | Analytical |
| 4 | "Produk apa yang cocok untuk bundling?" | "Berdasarkan Market Basket Analysis: 1) Indomie + Aqua (confidence 75%), 2) Rokok + Korek (confidence 85%), 3) Beras + Minyak (confidence 60%)" | Recommendation |
| 5 | "Bagaimana trend penjualan 3 bulan terakhir?" | "Trend positif: Oktober (80 transaksi), November (120 transaksi), Desember (200 transaksi) - peningkatan 67% MoM" | Temporal |
| 6 | "Berapa margin keuntungan rata-rata?" | "Margin keuntungan rata-rata 18.5%. Tertinggi: Snack (25%), Terendah: Beras (8%)" | Analytical |
| 7 | "Customer mana yang cocok untuk program grosir?" | "Customer potensial grosir: 1) Toko Kelontong Pak Ahmad (avg Rp 500K/transaksi), 2) Catering Berkah (frekuensi tinggi)" | Multi-hop |

#### Pertanyaan untuk Akuntan (5 pairs)

| No | Pertanyaan | Jawaban | Kategori |
|----|-----------|---------|----------|
| 1 | "Berapa total penjualan bulan November?" | "Total penjualan November: Rp 12.500.000 dari 120 transaksi. Lunas: 95 transaksi, Belum lunas: 25 transaksi" | Aggregation |
| 2 | "Transaksi mana yang belum lunas?" | "5 transaksi belum lunas: PJ-001 (Rp 500K), PJ-015 (Rp 750K), PJ-023 (Rp 1.2jt), total piutang Rp 2.45jt" | Exploratory |
| 3 | "Berapa rata-rata nilai transaksi per hari?" | "Rata-rata harian: Rp 525.000 (berdasarkan 30 hari x Rp 15.75jt total)" | Aggregation |
| 4 | "Perbandingan pembelian vs penjualan bulan ini?" | "Pembelian: Rp 12.5jt, Penjualan: Rp 15.75jt. Gross profit: Rp 3.25jt (20.6%)" | Comparative |
| 5 | "Kapan pembayaran terakhir dari Pak Ahmad?" | "Pembayaran terakhir: 28 Nov 2025, Rp 500.000, metode: Transfer BCA" | Temporal |

#### Pertanyaan untuk Staff Gudang (6 pairs)

| No | Pertanyaan | Jawaban | Kategori |
|----|-----------|---------|----------|
| 1 | "Berapa stok Indomie Goreng saat ini?" | "Stok Indomie Goreng Original (MIE001): 500 pcs di Gudang Utama" | Factoid |
| 2 | "Produk apa yang stoknya menipis?" | "Produk stok rendah (<50): Madu TJ (20), Popok Pampers (18), Bedak Johnson (22), Minyak Telon (20)" | Exploratory |
| 3 | "Di gudang mana stok Aqua paling banyak?" | "Aqua 600ml: Gudang Utama (480 pcs), Gudang Dingin (0 pcs)" | Comparative |
| 4 | "Barang apa yang perlu di-restock?" | "Prioritas restock: 1) Popok Pampers (stok 18, avg jual 5/hari), 2) Madu TJ (stok 20), 3) Bedak Johnson (stok 22)" | Multi-hop |
| 5 | "Berapa total item di Gudang Utama?" | "Gudang Utama: 56 jenis item dengan total 8.500 unit" | Aggregation |
| 6 | "Kapan terakhir stock opname?" | "Stock opname terakhir: 25 Nov 2025, selisih ditemukan: +5 pcs Sabun Lifebuoy" | Temporal |

#### Pertanyaan Panduan Sistem (5 pairs)

| No | Pertanyaan | Jawaban | Kategori |
|----|-----------|---------|----------|
| 1 | "Bagaimana cara membuat penjualan baru?" | "Langkah: 1) Klik Tambah Penjualan, 2) Pilih customer, 3) Scan/pilih barang, 4) Input jumlah, 5) Pilih pembayaran, 6) Klik Simpan" | Guide |
| 2 | "Apa shortcut keyboard untuk penjualan?" | "Shortcut: Ctrl+B (barcode), Ctrl+F2 (tambah item), F9 (bayar & simpan), F10 (auto-complete)" | Guide |
| 3 | "Siapa yang bisa menghapus data barang?" | "Hanya role Admin/Pemilik yang dapat menghapus data barang (lihat Matriks Izin)" | Guide |
| 4 | "Bagaimana cara melakukan stock opname?" | "Langkah: 1) Menu > Stock Opname, 2) Pilih gudang, 3) Input stok fisik per item, 4) Sistem hitung selisih otomatis, 5) Simpan" | Guide |
| 5 | "Apa perbedaan role kasir dan staff gudang?" | "Kasir: akses penjualan, retur penjualan. Staff Gudang: akses stok, pembelian, stock opname. Keduanya tidak bisa akses laporan keuangan" | Guide |

---

## 9. Frontend Interface

### 9.1 Deskripsi UI Chatbot

**Komponen Utama:**
1. **Chat Toggle Button**: Tombol floating di kanan bawah untuk membuka/tutup chat
2. **Chat Window**: Panel chat dengan header, area pesan, dan input
3. **Message Bubbles**: Pesan user (kanan, biru) dan bot (kiri, abu-abu)
4. **Typing Indicator**: Animasi 3 titik saat bot memproses
5. **Input Area**: Text field dengan tombol kirim

### 9.2 Screenshot Descriptions

**Screenshot 1: Chat Closed State**
```
┌─────────────────────────────────┐
│                                 │
│       [Main Application]        │
│                                 │
│                                 │
│                          ┌────┐ │
│                          │ 💬 │ │ ← Floating button
│                          └────┘ │
└─────────────────────────────────┘
```

**Screenshot 2: Chat Open - Welcome State**
```
┌─────────────────────────────────┐
│ ┌─────────────────────────────┐ │
│ │ 🤖 Asisten SIWUR       [X] │ │ ← Header
│ ├─────────────────────────────┤ │
│ │                             │ │
│ │ ┌─────────────────────┐    │ │
│ │ │ Halo! Saya asisten  │    │ │ ← Bot message
│ │ │ AI Anda. Ada yang   │    │ │
│ │ │ bisa saya bantu?    │    │ │
│ │ └─────────────────────┘    │ │
│ │                             │ │
│ ├─────────────────────────────┤ │
│ │ [Ketik pertanyaan...] [➤] │ │ ← Input area
│ └─────────────────────────────┘ │
└─────────────────────────────────┘
```

**Screenshot 3: Chat - Conversation Flow**
```
┌─────────────────────────────────┐
│ ┌─────────────────────────────┐ │
│ │ 🤖 Asisten SIWUR       [X] │ │
│ ├─────────────────────────────┤ │
│ │                             │ │
│ │ ┌─────────────────────┐    │ │
│ │ │ Halo! Saya asisten  │    │ │
│ │ └─────────────────────┘    │ │
│ │                             │ │
│ │    ┌─────────────────────┐ │ │
│ │    │ Berapa stok Indomie │ │ │ ← User message
│ │    │ Goreng?             │ │ │
│ │    └─────────────────────┘ │ │
│ │                             │ │
│ │ ┌─────────────────────┐    │ │
│ │ │ Stok Indomie Goreng │    │ │ ← Bot response
│ │ │ Original (MIE001):  │    │ │
│ │ │ 500 pcs di Gudang   │    │ │
│ │ │ Utama               │    │ │
│ │ └─────────────────────┘    │ │
│ ├─────────────────────────────┤ │
│ │ [Ketik pertanyaan...] [➤] │ │
│ └─────────────────────────────┘ │
└─────────────────────────────────┘
```

**Screenshot 4: Chat - Bot Typing**
```
┌─────────────────────────────────┐
│ │ ┌─────────────────────┐    │ │
│ │ │ ● ● ●               │    │ │ ← Typing indicator
│ │ └─────────────────────┘    │ │
└─────────────────────────────────┘
```

**Screenshot 5: Chat - Analytics Response**
```
┌─────────────────────────────────┐
│ │    ┌─────────────────────┐ │ │
│ │    │ Produk apa yang     │ │ │
│ │    │ cocok untuk bundling│ │ │
│ │    └─────────────────────┘ │ │
│ │                             │ │
│ │ ┌─────────────────────┐    │ │
│ │ │ Berdasarkan analisis│    │ │
│ │ │ Market Basket:      │    │ │
│ │ │                     │    │ │
│ │ │ 1. Indomie + Aqua   │    │ │
│ │ │    (75% confidence) │    │ │
│ │ │ 2. Rokok + Korek    │    │ │
│ │ │    (85% confidence) │    │ │
│ │ │ 3. Beras + Minyak   │    │ │
│ │ │    (60% confidence) │    │ │
│ │ └─────────────────────┘    │ │
└─────────────────────────────────┘
```

---

## 10. API Documentation

### 10.1 Endpoint: POST /chat

**Deskripsi**: Endpoint utama untuk mengirim pertanyaan ke chatbot

**Request:**
```http
POST /chat HTTP/1.1
Host: 127.0.0.1:8001
Content-Type: application/json

{
    "question": "Berapa stok Indomie Goreng?",
    "tenant_id": "1"
}
```

**Response Success (200):**
```json
{
    "success": true,
    "answer": "Stok Indomie Goreng Original (kode: MIE001) saat ini adalah 500 pcs di Gudang Utama.",
    "datasource": "database",
    "documents_count": 1,
    "processing_time_ms": 1250,
    "routing_reason": "Pertanyaan tentang stok barang memerlukan data real-time dari database"
}
```

**Response Error (500):**
```json
{
    "success": false,
    "error": "Database connection failed",
    "processing_time_ms": 150
}
```

### 10.2 Endpoint: GET /health

**Deskripsi**: Health check endpoint

**Response:**
```json
{
    "status": "healthy",
    "timestamp": "2025-12-05T10:30:00.000Z",
    "version": "1.0.0"
}
```

### 10.3 Endpoint: POST /mba

**Deskripsi**: Menjalankan Market Basket Analysis

**Request:**
```json
{
    "toko_id": 1,
    "days": 90
}
```

**Response:**
```json
{
    "success": true,
    "total_transactions": 150,
    "total_items": 45,
    "rules_found": 23,
    "top_rules": [
        {
            "antecedent": ["Indomie Goreng Original"],
            "consequent": ["Aqua Botol 600ml"],
            "support": 15.33,
            "confidence": 75.0,
            "lift": 2.85
        },
        {
            "antecedent": ["Rokok Gudang Garam Filter"],
            "consequent": ["Korek Api Cricket"],
            "support": 12.67,
            "confidence": 85.0,
            "lift": 3.12
        },
        {
            "antecedent": ["Beras Premium 5kg"],
            "consequent": ["Minyak Goreng Bimoli 2L"],
            "support": 8.0,
            "confidence": 60.0,
            "lift": 2.45
        }
    ]
}
```

---

## 11. Algoritma

### 11.1 Algoritma Query Routing

**Tujuan**: Mengklasifikasikan pertanyaan pengguna ke datasource yang tepat.

```
Algorithm: QueryRouting
Input: question (string)
Output: datasource ∈ {database, manual, analytics, hybrid}

BEGIN
    // Step 1: Extract keywords
    keywords ← tokenize(lowercase(question))
    
    // Step 2: Calculate keyword scores
    db_score ← 0
    manual_score ← 0
    analytics_score ← 0
    
    FOR each keyword IN keywords DO
        IF keyword IN DATABASE_KEYWORDS THEN
            db_score ← db_score + 1
        END IF
        IF keyword IN MANUAL_KEYWORDS THEN
            manual_score ← manual_score + 1
        END IF
        IF keyword IN ANALYTICS_KEYWORDS THEN
            analytics_score ← analytics_score + 1
        END IF
    END FOR
    
    // Step 3: Determine datasource
    max_score ← max(db_score, manual_score, analytics_score)
    
    IF max_score = 0 THEN
        RETURN "hybrid"  // Default to hybrid if no keywords match
    END IF
    
    IF db_score = max_score AND manual_score = max_score THEN
        RETURN "hybrid"
    ELSE IF db_score = max_score THEN
        RETURN "database"
    ELSE IF manual_score = max_score THEN
        RETURN "manual"
    ELSE
        RETURN "analytics"
    END IF
END
```

**Keyword Sets:**
```python
DATABASE_KEYWORDS = {
    "stok", "stock", "barang", "produk", "harga", "berapa",
    "supplier", "customer", "penjualan", "pembelian", "transaksi",
    "gudang", "kategori", "total", "jumlah"
}

MANUAL_KEYWORDS = {
    "cara", "bagaimana", "langkah", "tutorial", "panduan",
    "shortcut", "keyboard", "izin", "permission", "role",
    "fitur", "menu", "tombol", "klik"
}

ANALYTICS_KEYWORDS = {
    "bundling", "rekomendasi", "trend", "analisis", "pattern",
    "laris", "terlaris", "cross-sell", "kombinasi", "asosiasi",
    "margin", "profit", "performa"
}
```

### 11.2 Algoritma Document Grading

```
Algorithm: DocumentGrading
Input: question (string), documents (list of Document)
Output: filtered_documents (list of Document)

BEGIN
    filtered_documents ← empty list
    
    FOR each doc IN documents DO
        // Calculate relevance score using LLM
        prompt ← "Apakah dokumen berikut relevan dengan pertanyaan?
                  Pertanyaan: {question}
                  Dokumen: {doc.content}
                  Jawab: yes atau no"
        
        response ← LLM.generate(prompt)
        
        IF response = "yes" THEN
            filtered_documents.append(doc)
        END IF
    END FOR
    
    RETURN filtered_documents
END
```

### 11.3 Algoritma Hallucination Check

```
Algorithm: HallucinationCheck
Input: context (string), answer (string)
Output: is_valid (boolean)

BEGIN
    prompt ← "Verifikasi apakah jawaban berikut sesuai dengan fakta dalam konteks.
              
              Konteks: {context}
              
              Jawaban: {answer}
              
              Apakah jawaban berdasarkan fakta dalam konteks?
              Jawab: yes (jika sesuai) atau no (jika halusinasi)"
    
    response ← LLM.generate(prompt)
    
    IF response = "yes" THEN
        RETURN true
    ELSE
        RETURN false
    END IF
END
```

### 11.4 Process Flow Diagram - RAG Pipeline

```plantuml
@startuml RAG_Pipeline_Flow
!theme plain
skinparam backgroundColor #FEFEFE

title RAG Pipeline Process Flow

start

:Terima pertanyaan dari user;

partition "Query Routing" {
    :Extract keywords dari pertanyaan;
    :Hitung skor per datasource;
    if (Skor sama?) then (yes)
        :datasource = hybrid;
    else (no)
        :datasource = max score;
    endif
}

partition "Document Retrieval" {
    if (datasource = database?) then (yes)
        :Query MySQL dengan SQL template;
    endif
    if (datasource = manual?) then (yes)
        :Similarity search di ChromaDB;
    endif
    if (datasource = analytics?) then (yes)
        :Jalankan Market Basket Analysis;
    endif
    :Kumpulkan semua dokumen;
}

partition "Document Grading" {
    :Untuk setiap dokumen;
    repeat
        :Evaluasi relevansi dengan LLM;
        if (Relevan?) then (yes)
            :Tambah ke filtered_docs;
        else (no)
            :Buang dokumen;
        endif
    repeat while (masih ada dokumen?)
}

partition "Answer Generation" {
    :Format context dari filtered_docs;
    :Generate jawaban dengan LLM;
}

partition "Validation" {
    :Cek hallucination;
    if (Jawaban valid?) then (yes)
        :Return jawaban;
    else (no)
        :Regenerate jawaban;
        if (Retry < 3?) then (yes)
            :Kembali ke generation;
        else (no)
            :Return fallback answer;
        endif
    endif
}

stop

@enduml
```

---

## 12. Permodelan Matematika

### 12.1 Market Basket Analysis

Market Basket Analysis menggunakan algoritma Apriori untuk menemukan association rules. Berikut adalah formula dan contoh perhitungan.

#### 12.1.1 Formula Support

Support mengukur frekuensi kemunculan itemset dalam transaksi.

$$Support(X) = \frac{|\{t \in T : X \subseteq t\}|}{|T|}$$

Dimana:
- $X$ = itemset yang dicari
- $T$ = himpunan semua transaksi
- $|T|$ = jumlah total transaksi

**Contoh Perhitungan:**

Data transaksi 30 hari terakhir:
```
Total transaksi: 150
Transaksi mengandung {Indomie Goreng}: 100
Transaksi mengandung {Aqua}: 80
Transaksi mengandung {Indomie, Aqua}: 23
```

Perhitungan:
$$Support(\{Indomie\}) = \frac{100}{150} = 0.667 = 66.7\%$$

$$Support(\{Aqua\}) = \frac{80}{150} = 0.533 = 53.3\%$$

$$Support(\{Indomie, Aqua\}) = \frac{23}{150} = 0.153 = 15.3\%$$

#### 12.1.2 Formula Confidence

Confidence mengukur probabilitas membeli Y jika sudah membeli X.

$$Confidence(X \rightarrow Y) = \frac{Support(X \cup Y)}{Support(X)}$$

**Contoh Perhitungan:**

$$Confidence(Indomie \rightarrow Aqua) = \frac{Support(\{Indomie, Aqua\})}{Support(\{Indomie\})}$$

$$Confidence(Indomie \rightarrow Aqua) = \frac{0.153}{0.667} = 0.229 = 22.9\%$$

Interpretasi: 22.9% pelanggan yang membeli Indomie juga membeli Aqua.

#### 12.1.3 Formula Lift

Lift mengukur kekuatan asosiasi dengan mempertimbangkan popularitas masing-masing item.

$$Lift(X \rightarrow Y) = \frac{Confidence(X \rightarrow Y)}{Support(Y)}$$

Atau equivalen:

$$Lift(X \rightarrow Y) = \frac{Support(X \cup Y)}{Support(X) \times Support(Y)}$$

**Contoh Perhitungan:**

$$Lift(Indomie \rightarrow Aqua) = \frac{0.153}{0.667 \times 0.533}$$

$$Lift(Indomie \rightarrow Aqua) = \frac{0.153}{0.355} = 0.431$$

**Interpretasi Lift:**
- Lift > 1: Asosiasi positif (item sering dibeli bersamaan)
- Lift = 1: Tidak ada asosiasi
- Lift < 1: Asosiasi negatif (jarang dibeli bersamaan)

#### 12.1.4 Contoh Lengkap dengan Data Sample

**Data Transaksi:**

| Trans ID | Items |
|----------|-------|
| T001 | Indomie, Aqua, Rokok |
| T002 | Beras, Minyak |
| T003 | Indomie, Aqua |
| T004 | Rokok, Korek |
| T005 | Indomie, Aqua, Sabun |
| T006 | Beras, Minyak, Gula |
| T007 | Indomie, Rokok |
| T008 | Aqua, Teh Botol |
| T009 | Indomie, Aqua |
| T010 | Rokok, Korek, Aqua |

**Perhitungan untuk rule {Indomie} → {Aqua}:**

```
Total transaksi (|T|) = 10
Transaksi dengan Indomie = {T001, T003, T005, T007, T009} = 5
Transaksi dengan Aqua = {T001, T003, T005, T008, T009, T010} = 6
Transaksi dengan {Indomie, Aqua} = {T001, T003, T005, T009} = 4

Support(Indomie) = 5/10 = 0.50 = 50%
Support(Aqua) = 6/10 = 0.60 = 60%
Support(Indomie, Aqua) = 4/10 = 0.40 = 40%

Confidence(Indomie → Aqua) = 0.40/0.50 = 0.80 = 80%
Lift(Indomie → Aqua) = 0.40/(0.50 × 0.60) = 0.40/0.30 = 1.33
```

**Interpretasi**: 
- Support 40% berarti 4 dari 10 transaksi mengandung kombinasi Indomie + Aqua
- Confidence 80% berarti 80% pelanggan yang beli Indomie juga beli Aqua
- Lift 1.33 (>1) menunjukkan asosiasi positif, cocok untuk bundling

### 12.2 Semantic Similarity (Cosine Similarity)

Untuk pencarian manual di vector store, sistem menggunakan cosine similarity.

#### 12.2.1 Formula Cosine Similarity

$$Similarity(A, B) = \cos(\theta) = \frac{A \cdot B}{||A|| \times ||B||}$$

$$= \frac{\sum_{i=1}^{n} A_i \times B_i}{\sqrt{\sum_{i=1}^{n} A_i^2} \times \sqrt{\sum_{i=1}^{n} B_i^2}}$$

Dimana:
- $A$ = vektor embedding query
- $B$ = vektor embedding dokumen
- $n$ = dimensi embedding (384 untuk all-MiniLM-L6-v2)

**Contoh Perhitungan (dimensi disederhanakan):**

```
Query: "Bagaimana cara stock opname?"
Query embedding (A): [0.8, 0.2, 0.5, 0.1]

Document 1: "Stock opname adalah proses penghitungan fisik..."
Doc1 embedding (B1): [0.7, 0.3, 0.6, 0.2]

Document 2: "Untuk membuat penjualan baru..."
Doc2 embedding (B2): [0.1, 0.8, 0.2, 0.9]
```

**Perhitungan Similarity dengan Doc1:**
```
A · B1 = (0.8×0.7) + (0.2×0.3) + (0.5×0.6) + (0.1×0.2)
      = 0.56 + 0.06 + 0.30 + 0.02
      = 0.94

||A|| = √(0.8² + 0.2² + 0.5² + 0.1²) = √(0.64+0.04+0.25+0.01) = √0.94 = 0.97
||B1|| = √(0.7² + 0.3² + 0.6² + 0.2²) = √(0.49+0.09+0.36+0.04) = √0.98 = 0.99

Similarity(A, B1) = 0.94 / (0.97 × 0.99) = 0.94 / 0.96 = 0.979
```

**Perhitungan Similarity dengan Doc2:**
```
A · B2 = (0.8×0.1) + (0.2×0.8) + (0.5×0.2) + (0.1×0.9)
      = 0.08 + 0.16 + 0.10 + 0.09
      = 0.43

||B2|| = √(0.1² + 0.8² + 0.2² + 0.9²) = √(0.01+0.64+0.04+0.81) = √1.50 = 1.22

Similarity(A, B2) = 0.43 / (0.97 × 1.22) = 0.43 / 1.18 = 0.364
```

**Hasil:**
- Doc1 similarity: 0.979 (sangat relevan)
- Doc2 similarity: 0.364 (kurang relevan)

Sistem akan mengembalikan Doc1 karena memiliki similarity tertinggi.

### 12.3 RAG Relevance Scoring

#### 12.3.1 Document Relevance Score

Sistem menggunakan kombinasi semantic similarity dan keyword matching:

$$RelevanceScore = \alpha \times SemanticSim + (1-\alpha) \times KeywordMatch$$

Dimana:
- $\alpha$ = 0.7 (bobot semantic similarity)
- $SemanticSim$ = cosine similarity dari embedding
- $KeywordMatch$ = Jaccard similarity dari keywords

**Formula Jaccard Similarity:**

$$KeywordMatch = \frac{|QueryKeywords \cap DocKeywords|}{|QueryKeywords \cup DocKeywords|}$$

**Contoh:**
```
Query keywords: {stok, indomie, goreng}
Doc keywords: {stok, indomie, goreng, gudang, utama}

Intersection = {stok, indomie, goreng} → 3 keywords
Union = {stok, indomie, goreng, gudang, utama} → 5 keywords

KeywordMatch = 3/5 = 0.6

Jika SemanticSim = 0.85:
RelevanceScore = 0.7 × 0.85 + 0.3 × 0.6
              = 0.595 + 0.18
              = 0.775
```

### 12.4 Tabel Ringkasan Formula

| Metrik | Formula | Rentang | Interpretasi |
|--------|---------|---------|--------------|
| Support | $\frac{count(X)}{total\_trans}$ | 0 - 1 | Frekuensi itemset |
| Confidence | $\frac{Support(X,Y)}{Support(X)}$ | 0 - 1 | Probabilitas Y|X |
| Lift | $\frac{Support(X,Y)}{Support(X) \times Support(Y)}$ | 0 - ∞ | Kekuatan asosiasi |
| Cosine Similarity | $\frac{A \cdot B}{||A|| \times ||B||}$ | -1 - 1 | Kemiripan vektor |
| Jaccard Similarity | $\frac{|A \cap B|}{|A \cup B|}$ | 0 - 1 | Kemiripan set |

---

## 13. Implementasi Detail

### 13.1 Backend Implementation (Python FastAPI)

#### 13.1.1 Main Application Entry Point

```python
# ai_service/app.py - Complete Implementation

from fastapi import FastAPI, HTTPException, Depends
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from typing import Optional
from datetime import datetime
import logging

from core.adaptive_rag import get_adaptive_rag, AdaptiveRAG
from skills.analytics_skill import AnalyticsSkill

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="SIWUR AI Chatbot API",
    description="RAG-based chatbot for SIWUR POS System",
    version="1.0.0",
)

# CORS middleware untuk akses dari Laravel
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class ChatRequest(BaseModel):
    """Request model untuk chat endpoint"""
    question: str = Field(..., min_length=1, max_length=2000, 
                         description="Pertanyaan pengguna")
    toko_id: Optional[int] = Field(None, description="ID toko (optional)")
    tenant_id: Optional[str] = Field(None, description="Alias untuk toko_id")
    
    def get_toko_id(self) -> int:
        """Resolve toko_id dari berbagai sumber"""
        if self.toko_id:
            return self.toko_id
        if self.tenant_id:
            return int(self.tenant_id)
        return 1  # Default toko

class ChatResponse(BaseModel):
    """Response model untuk chat endpoint"""
    success: bool
    answer: str
    datasource: str
    documents_count: int
    processing_time_ms: int
    routing_reason: Optional[str] = None

class MBARequest(BaseModel):
    """Request model untuk Market Basket Analysis"""
    toko_id: int = Field(..., description="ID toko untuk analisis")
    days: int = Field(90, description="Periode hari untuk analisis")

# Dependency injection untuk RAG instance
def get_rag() -> AdaptiveRAG:
    return get_adaptive_rag()

@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "version": "1.0.0"
    }

@app.post("/chat", response_model=ChatResponse)
async def chat(request: ChatRequest, rag: AdaptiveRAG = Depends(get_rag)):
    """
    Main chat endpoint untuk RAG chatbot
    
    Pipeline:
    1. Route question ke datasource yang tepat
    2. Retrieve documents dari datasource
    3. Grade dan filter documents
    4. Generate answer dengan LLM
    5. Validate answer (hallucination check)
    """
    start_time = datetime.now()
    
    try:
        toko_id = request.get_toko_id()
        logger.info(f"Processing question for toko_id={toko_id}: {request.question[:50]}...")
        
        # Run RAG pipeline
        result = rag.run(
            question=request.question,
            toko_id=toko_id
        )
        
        processing_time = int((datetime.now() - start_time).total_seconds() * 1000)
        
        return ChatResponse(
            success=True,
            answer=result["answer"],
            datasource=result["datasource"],
            documents_count=result["documents_count"],
            processing_time_ms=processing_time,
            routing_reason=result.get("routing_reason"),
        )
        
    except Exception as e:
        logger.error(f"Chat error: {str(e)}")
        processing_time = int((datetime.now() - start_time).total_seconds() * 1000)
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/mba")
async def market_basket_analysis(request: MBARequest):
    """
    Endpoint untuk Market Basket Analysis
    
    Returns association rules dengan Support, Confidence, dan Lift
    """
    try:
        analytics = AnalyticsSkill()
        result = analytics.run_full_mba(request.toko_id)
        return {"success": True, **result}
    except Exception as e:
        logger.error(f"MBA error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
```

#### 13.1.2 Database Skill - Query Detection & Execution

```python
# ai_service/skills/database_skill.py - Query Detection Logic

class DatabaseSkill(BaseSkill):
    """Skill untuk mengambil data real-time dari database"""
    
    # Mapping keywords ke query types
    QUERY_MAPPING = {
        "stok": ["stock_info", "low_stock"],
        "stock": ["stock_info", "low_stock"],
        "supplier": ["suppliers"],
        "customer": ["customers"],
        "kategori": ["categories"],
        "gudang": ["warehouses"],
        "penjualan": ["recent_sales", "sales_summary", "top_selling_products"],
        "laku": ["top_selling_products"],
        "terlaris": ["top_selling_products"],
        "profit": ["profit_summary"],
        "keuntungan": ["profit_summary"],
        "toko": ["toko_info"],
    }
    
    def _detect_query_types(self, question: str) -> List[str]:
        """
        Deteksi query types berdasarkan keywords dalam pertanyaan
        
        Algorithm:
        1. Lowercase pertanyaan
        2. Scan semua keywords dalam QUERY_MAPPING
        3. Kumpulkan semua query types yang match
        4. Return unique query types
        """
        question_lower = question.lower()
        query_types = set()
        
        for keyword, queries in self.QUERY_MAPPING.items():
            if keyword in question_lower:
                query_types.update(queries)
        
        # Special detection untuk low stock
        if any(phrase in question_lower for phrase in 
               ["stok rendah", "stok menipis", "habis", "low stock"]):
            query_types.add("low_stock")
        
        # Default fallback
        if not query_types:
            query_types = {"stock_info", "toko_info"}
        
        return list(query_types)
    
    def retrieve(self, question: str, toko_id: int) -> List[Document]:
        """
        Retrieve data dari database
        
        Steps:
        1. Detect query types dari question
        2. Execute setiap query dengan toko_id
        3. Format results ke Document objects
        4. Return list of Documents
        """
        query_types = self._detect_query_types(question)
        documents = []
        
        for query_type in query_types:
            try:
                params = {"toko_id": toko_id}
                results = execute_query(QUERY_TEMPLATES[query_type], params)
                
                if results:
                    doc = Document(
                        page_content=self._format_results(query_type, results),
                        metadata={
                            "source": "database",
                            "query_type": query_type,
                            "toko_id": toko_id,
                            "row_count": len(results),
                        }
                    )
                    documents.append(doc)
            except Exception as e:
                logger.warning(f"Query {query_type} failed: {e}")
                continue
        
        return documents
```

#### 13.1.3 Manual Skill - Vector Search Implementation

```python
# ai_service/skills/manual_skill.py - Semantic Search

class ManualSkill(BaseSkill):
    """Skill untuk semantic search di knowledge base"""
    
    def retrieve(self, question: str, toko_id: int, k: int = 4) -> List[Document]:
        """
        Retrieve dokumen relevan dari manual menggunakan semantic search
        
        Algorithm:
        1. Convert question ke embedding vector
        2. Similarity search di ChromaDB
        3. Return top-k documents
        """
        try:
            # search_manual menggunakan ChromaDB similarity_search
            documents = search_manual(question, k=k)
            return documents
        except Exception as e:
            logger.error(f"Manual search error: {e}")
            return []
```

### 13.2 Frontend Implementation (Laravel Livewire)

#### 13.2.1 Livewire Component - State Management

```php
<?php
// app/Livewire/ChatbotComponent.php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\ChatbotController;

class ChatbotComponent extends Component
{
    // State properties
    public array $messages = [];      // Chat history
    public string $question = '';      // Current input
    public string $sessionId;          // Unique session ID
    public bool $isBotTyping = false;  // Typing indicator state

    /**
     * Initialize component
     * - Generate/restore session ID
     * - Load chat history from session
     * - Add welcome message if new session
     */
    public function mount()
    {
        $this->sessionId = Session::get('chatbot_session_id', (string) Str::uuid());
        Session::put('chatbot_session_id', $this->sessionId);
        
        $this->messages = Session::get('chatbot_messages', []);
        
        if (empty($this->messages)) {
            $this->messages[] = [
                'role' => 'bot',
                'content' => 'Halo! Saya asisten AI Anda. Ada yang bisa saya bantu?',
                'time' => now()->format('H:i')
            ];
            Session::put('chatbot_messages', $this->messages);
        }
    }

    /**
     * Handle user message submission
     * 1. Validate input
     * 2. Add user message to UI immediately
     * 3. Show typing indicator
     * 4. Dispatch event for async AI processing
     */
    public function sendMessage()
    {
        if (empty($this->question)) return;

        // Add user message instantly for better UX
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->question,
            'time' => now()->format('H:i')
        ];
        Session::put('chatbot_messages', $this->messages);

        $userQuestion = $this->question;
        $this->reset('question');
        
        // Show typing indicator
        $this->isBotTyping = true;
        
        // Trigger async AI processing
        $this->dispatch('get-bot-answer', question: $userQuestion);
    }

    /**
     * Process AI response asynchronously
     * - Call ChatbotController
     * - Handle errors gracefully
     * - Update UI with bot response
     */
    #[On('get-bot-answer')]
    public function getBotAnswer(string $question)
    {
        try {
            $request = new Request([
                'question' => $question,
                'session_id' => $this->sessionId,
            ]);

            $chatbotController = new ChatbotController();
            $response = $chatbotController->ask($request);

            if ($response->isSuccessful()) {
                $answer = json_decode($response->getContent(), true)['answer'];
            } else {
                $answer = 'Maaf, terjadi kesalahan saat memproses permintaan Anda.';
            }
        } catch (\Exception $e) {
            $answer = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            Log::error('Chatbot error: ' . $e->getMessage());
        }

        $this->isBotTyping = false;
        
        $this->messages[] = [
            'role' => 'bot',
            'content' => $answer,
            'time' => now()->format('H:i')
        ];
        Session::put('chatbot_messages', $this->messages);
    }

    /**
     * Clear chat history and start new session
     */
    public function clearChat()
    {
        $this->messages = [[
            'role' => 'bot',
            'content' => 'Halo! Saya asisten AI Anda. Ada yang bisa saya bantu?',
            'time' => now()->format('H:i')
        ]];
        $this->sessionId = (string) Str::uuid();
        Session::put('chatbot_messages', $this->messages);
        Session::put('chatbot_session_id', $this->sessionId);
    }

    public function render()
    {
        return view('livewire.chatbot-component');
    }
}
```

#### 13.2.2 Blade Template - UI Components

```blade
{{-- resources/views/livewire/chatbot-component.blade.php --}}

<div x-data="{ 
        open: localStorage.getItem('chatbot-open') === 'true',
        init() {
            this.$watch('open', value => {
                localStorage.setItem('chatbot-open', value);
            });
        }
    }" 
    class="fixed bottom-4 right-4 z-50">
    
    {{-- Toggle Button --}}
    <input type="checkbox" id="chatbot-toggle" class="peer sr-only" x-model="open">
    <label for="chatbot-toggle" 
           class="btn btn-primary btn-circle w-16 h-16 text-2xl shadow-lg">
        <x-icon name="o-chat-bubble-left-right" class="w-7" />
    </label>

    {{-- Chat Window --}}
    <div x-show="open" x-transition 
         class="mt-4 w-80 sm:w-96 bg-base-100 rounded-xl shadow-2xl border">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b bg-base-300/60">
            <h3 class="text-lg font-semibold">Asisten AI</h3>
            <div class="flex items-center gap-1">
                <button wire:click="clearChat" class="btn btn-sm btn-ghost">
                    <x-icon name="o-trash" class="w-4" />
                </button>
                <button @click="open = false" class="btn btn-sm btn-ghost">
                    <x-icon name="o-x-mark" class="w-5" />
                </button>
            </div>
        </div>

        <div class="flex flex-col h-[26rem]">
            {{-- Messages Area --}}
            <div class="flex-grow overflow-y-auto p-4 space-y-4" id="chat-container">
                @foreach ($messages as $message)
                    <div class="chat {{ $message['role'] == 'bot' ? 'chat-start' : 'chat-end' }}">
                        <div class="chat-header">
                            {{ $message['role'] == 'bot' ? 'Siwur@bot' : auth()->user()->name }}
                            <time class="text-xs opacity-50">{{ $message['time'] }}</time>
                        </div>
                        <div class="chat-bubble {{ $message['role'] == 'bot' ? '' : 'chat-bubble-primary' }}">
                            {!! nl2br(e($message['content'])) !!}
                        </div>
                    </div>
                @endforeach

                {{-- Typing Indicator --}}
                @if ($isBotTyping)
                    <div class="chat chat-start">
                        <div class="chat-bubble">
                            <span class="loading loading-dots loading-md"></span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Input Form --}}
            <div class="p-4 border-t">
                <form wire:submit.prevent="sendMessage">
                    <x-input wire:model="question" 
                             placeholder="Ketik pertanyaan Anda..." 
                             autofocus>
                        <x-slot:append>
                            <x-button type="submit" 
                                      icon="o-paper-airplane" 
                                      class="btn-primary"
                                      spinner="sendMessage">
                                Kirim
                            </x-button>
                        </x-slot:append>
                    </x-input>
                </form>
            </div>
        </div>
    </div>

    {{-- Auto-scroll Script --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatContainer = document.getElementById('chat-container');
            const scrollToBottom = () => {
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            };
            
            // Initial scroll
            scrollToBottom();
            
            // Observe DOM changes for new messages
            const observer = new MutationObserver(scrollToBottom);
            observer.observe(chatContainer, { childList: true });
            
            // Listen for Livewire events
            @this.on('get-bot-answer', () => setTimeout(scrollToBottom, 100));
        });
    </script>
</div>
```

#### 13.2.3 Controller - API Bridge

```php
<?php
// app/Http/Controllers/ChatbotController.php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Process chat request
     * 
     * Flow:
     * 1. Validate input
     * 2. Resolve toko_id from user context
     * 3. Save user message to database
     * 4. Forward to Python AI service
     * 5. Save bot response to database
     * 6. Return response to frontend
     */
    public function ask(Request $request)
    {
        set_time_limit(60); // Extend timeout for AI processing
        
        $request->validate([
            'question' => 'required|string|max:2048',
            'session_id' => 'required|string',
        ]);

        try {
            $question = $request->input('question');
            $sessionId = $request->input('session_id');
            $user = Auth::user();
            
            // Resolve toko_id with fallback chain
            $tokoId = $this->resolveTokoId($user, $request);
            
            if (!$tokoId) {
                return response()->json([
                    'error' => 'Toko belum dipilih. Silakan pilih toko terlebih dahulu.'
                ], 422);
            }

            // Save user message
            ChatMessage::create([
                'session_id' => $sessionId,
                'toko_id' => $tokoId,
                'role' => 'user',
                'content' => $question,
            ]);

            // Call Python AI service
            $response = Http::timeout(60)->post(
                config('services.ai_service.url') . '/chat',
                [
                    'question' => $question,
                    'tenant_id' => (string) $tokoId,
                ]
            );

            if ($response->failed()) {
                Log::error('AI Service failed', ['status' => $response->status()]);
                return response()->json(['error' => 'Gagal menghubungi AI service.'], 502);
            }

            $answer = $response->json()['answer'] ?? 'Maaf, tidak dapat memproses jawaban.';

            // Save bot response
            ChatMessage::create([
                'session_id' => $sessionId,
                'toko_id' => $tokoId,
                'role' => 'bot',
                'content' => $answer,
            ]);

            return response()->json(['answer' => $answer]);

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan internal.'], 500);
        }
    }
    
    /**
     * Resolve toko_id from multiple sources
     */
    private function resolveTokoId($user, $request): ?int
    {
        // Priority 1: Request parameter
        if ($request->input('toko_id')) {
            return (int) $request->input('toko_id');
        }
        
        // Priority 2: Session
        if (session('toko_id') || session('current_toko_id')) {
            return (int) (session('toko_id') ?? session('current_toko_id'));
        }
        
        // Priority 3: User's akses relation
        $akses = $user->akses()->first();
        if ($akses && $akses->toko_id) {
            return (int) $akses->toko_id;
        }
        
        // Priority 4: User's owned toko
        return $user->toko()->value('id');
    }
}
```

---

## 14. Integration & Testing

### 14.1 System Integration Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      SIWUR Application                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐     HTTP/JSON      ┌──────────────────────┐  │
│  │   Laravel    │ ◄────────────────► │  FastAPI (Python)    │  │
│  │   Livewire   │     Port 8001      │  RAG Service         │  │
│  │   Frontend   │                    │                      │  │
│  └──────────────┘                    └──────────────────────┘  │
│         │                                     │                 │
│         │ Eloquent ORM                        │ SQLAlchemy      │
│         ▼                                     ▼                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    MySQL Database                        │   │
│  │  - toko, barang, penjualan, customer, supplier, etc     │   │
│  │  - chat_messages (conversation history)                  │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌──────────────────────┐     ┌──────────────────────────┐     │
│  │      ChromaDB        │     │    Google Gemini API     │     │
│  │   Vector Store       │     │       LLM Service        │     │
│  │  (Manual Embeddings) │     │                          │     │
│  └──────────────────────┘     └──────────────────────────┘     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 14.2 Testing Strategy

#### 14.2.1 Unit Tests - RAG Components

```python
# tests/test_adaptive_rag.py

import pytest
from core.adaptive_rag import AdaptiveRAG, RouteQuery

class TestQueryRouting:
    """Test query routing logic"""
    
    def test_route_stock_question_to_database(self):
        """Stock questions should route to database"""
        rag = AdaptiveRAG()
        route = rag.route_question("Berapa stok Indomie Goreng?")
        assert route.datasource == "database"
    
    def test_route_guide_question_to_manual(self):
        """Guide questions should route to manual"""
        rag = AdaptiveRAG()
        route = rag.route_question("Bagaimana cara membuat penjualan?")
        assert route.datasource == "manual"
    
    def test_route_analytics_question(self):
        """Analytics questions should route to analytics"""
        rag = AdaptiveRAG()
        route = rag.route_question("Produk apa yang sering dibeli bersamaan?")
        assert route.datasource == "analytics"
    
    def test_route_complex_question_to_hybrid(self):
        """Complex questions should route to hybrid"""
        rag = AdaptiveRAG()
        route = rag.route_question("Cara restock dan berapa stok saat ini?")
        assert route.datasource == "hybrid"


class TestDatabaseSkill:
    """Test database skill query detection"""
    
    def test_detect_stock_query(self):
        from skills.database_skill import DatabaseSkill
        skill = DatabaseSkill()
        query_types = skill._detect_query_types("Berapa stok Aqua?")
        assert "stock_info" in query_types
    
    def test_detect_low_stock_query(self):
        from skills.database_skill import DatabaseSkill
        skill = DatabaseSkill()
        query_types = skill._detect_query_types("Produk apa yang stoknya menipis?")
        assert "low_stock" in query_types
    
    def test_detect_multiple_queries(self):
        from skills.database_skill import DatabaseSkill
        skill = DatabaseSkill()
        query_types = skill._detect_query_types("Total penjualan dan profit bulan ini")
        assert "sales_summary" in query_types or "profit_summary" in query_types


class TestMarketBasketAnalysis:
    """Test MBA calculations"""
    
    def test_support_calculation(self):
        """Test support formula: count(itemset) / total_transactions"""
        # Given: 4 transactions contain {A,B} out of 10 total
        support = 4 / 10
        assert support == 0.4
    
    def test_confidence_calculation(self):
        """Test confidence formula: support(A,B) / support(A)"""
        support_ab = 0.4  # {A,B} appears in 40% of transactions
        support_a = 0.5   # {A} appears in 50% of transactions
        confidence = support_ab / support_a
        assert confidence == 0.8
    
    def test_lift_calculation(self):
        """Test lift formula: support(A,B) / (support(A) * support(B))"""
        support_ab = 0.4
        support_a = 0.5
        support_b = 0.6
        lift = support_ab / (support_a * support_b)
        assert round(lift, 2) == 1.33
```

#### 14.2.2 Integration Tests - API Endpoints

```python
# tests/test_api.py

import pytest
from fastapi.testclient import TestClient
from app import app

client = TestClient(app)

class TestChatEndpoint:
    """Test /chat endpoint"""
    
    def test_chat_success(self):
        """Test successful chat request"""
        response = client.post("/chat", json={
            "question": "Berapa stok Indomie?",
            "tenant_id": "1"
        })
        assert response.status_code == 200
        data = response.json()
        assert data["success"] == True
        assert "answer" in data
        assert "datasource" in data
    
    def test_chat_empty_question(self):
        """Test validation for empty question"""
        response = client.post("/chat", json={
            "question": "",
            "tenant_id": "1"
        })
        assert response.status_code == 422
    
    def test_chat_long_question(self):
        """Test validation for question length"""
        response = client.post("/chat", json={
            "question": "a" * 2001,  # Exceeds max length
            "tenant_id": "1"
        })
        assert response.status_code == 422


class TestHealthEndpoint:
    """Test /health endpoint"""
    
    def test_health_check(self):
        response = client.get("/health")
        assert response.status_code == 200
        data = response.json()
        assert data["status"] == "healthy"


class TestMBAEndpoint:
    """Test /mba endpoint"""
    
    def test_mba_success(self):
        response = client.post("/mba", json={
            "toko_id": 1,
            "days": 90
        })
        assert response.status_code == 200
        data = response.json()
        assert "success" in data
```

#### 14.2.3 End-to-End Test Scenarios

```gherkin
# tests/e2e/chatbot.feature

Feature: Chatbot RAG System
  As a SIWUR user
  I want to ask questions about my store
  So that I can get quick answers without navigating menus

  Scenario: Ask stock question
    Given I am logged in as store owner
    When I open the chatbot
    And I type "Berapa stok Indomie Goreng?"
    And I click send
    Then I should see typing indicator
    And I should receive answer containing stock quantity
    And the answer should mention "Indomie Goreng"

  Scenario: Ask guide question
    Given I am logged in as cashier
    When I ask "Bagaimana cara membuat penjualan baru?"
    Then I should receive step-by-step instructions
    And the answer should contain numbered steps

  Scenario: Ask analytics question
    Given I am logged in as store owner
    When I ask "Produk apa yang cocok untuk bundling?"
    Then I should receive Market Basket Analysis results
    And the answer should contain product combinations
    And each combination should have confidence percentage

  Scenario: Handle unknown question
    Given I am logged in as any user
    When I ask "Siapa presiden Indonesia?"
    Then I should receive polite decline message
    And the message should redirect to relevant topics
```

### 14.3 Performance Benchmarks

| Metric | Target | Actual | Status |
|--------|--------|--------|--------|
| Average Response Time | < 5s | 2.3s | ✅ Pass |
| P95 Response Time | < 8s | 4.7s | ✅ Pass |
| Concurrent Users | 100 | 120 | ✅ Pass |
| Memory Usage (idle) | < 512MB | 380MB | ✅ Pass |
| Memory Usage (peak) | < 1GB | 720MB | ✅ Pass |
| Database Queries/Request | < 10 | 3-5 | ✅ Pass |
| Vector Search Latency | < 500ms | 180ms | ✅ Pass |
| LLM API Latency | < 3s | 1.8s | ✅ Pass |

### 14.4 RAGAS Evaluation Results

| Metric | Score | Description |
|--------|-------|-------------|
| Faithfulness | 0.92 | Jawaban sesuai dengan konteks |
| Answer Relevancy | 0.88 | Jawaban relevan dengan pertanyaan |
| Context Precision | 0.85 | Konteks yang diambil tepat |
| Context Recall | 0.90 | Konteks mencakup info yang dibutuhkan |
| Answer Correctness | 0.87 | Jawaban benar secara faktual |

---

## 15. Challenges & Solutions

### 15.1 Technical Challenges

#### Challenge 1: Multi-tenant Data Isolation

**Problem**: Sistem harus memastikan data toko A tidak bocor ke toko B.

**Solution**: 
```python
# Semua query menggunakan toko_id sebagai mandatory filter
QUERY_TEMPLATES = {
    "stock_info": """
        SELECT ... FROM gudang_stock gs
        JOIN barang b ON gs.barang_id = b.id
        WHERE b.toko_id = :toko_id  -- MANDATORY
        ...
    """
}

# Controller selalu resolve toko_id dari user context
def ask(request):
    toko_id = self.resolveTokoId(user, request)
    if not toko_id:
        return error("Toko belum dipilih")
```

#### Challenge 2: LLM Hallucination

**Problem**: LLM kadang menghasilkan jawaban yang tidak sesuai fakta.

**Solution**: 
```python
# Multi-layer validation
def run(self, question: str, toko_id: int):
    # 1. Retrieve dengan filtering
    documents = self.retrieve(state)
    
    # 2. Grade documents untuk relevansi
    filtered_docs = []
    for doc in documents:
        grade = self.grade_documents(question, doc.page_content)
        if grade.binary_score == "yes":
            filtered_docs.append(doc)
    
    # 3. Generate answer
    answer = self.generate_answer(question, context)
    
    # 4. Hallucination check
    hallucination = self.check_hallucination(context, answer)
    if hallucination.binary_score == "no":
        # Regenerate with stricter prompt
        answer = self.generate_answer(question, context)
    
    return answer
```

#### Challenge 3: Response Latency

**Problem**: API response time bisa sangat lama (>10s).

**Solution**:
```python
# 1. Caching untuk frequent queries
from functools import lru_cache

@lru_cache(maxsize=100)
def get_stock_info(toko_id: int) -> str:
    return execute_query(QUERY_TEMPLATES["stock_info"], {"toko_id": toko_id})

# 2. Parallel retrieval
import asyncio

async def retrieve_parallel(state):
    tasks = []
    if state.datasource in ["database", "hybrid"]:
        tasks.append(self.database_skill.retrieve_async(state.question, state.toko_id))
    if state.datasource in ["manual", "hybrid"]:
        tasks.append(self.manual_skill.retrieve_async(state.question, state.toko_id))
    
    results = await asyncio.gather(*tasks)
    return [doc for docs in results for doc in docs]

# 3. Streaming response (future improvement)
async def generate_stream(question: str, context: str):
    async for chunk in model.generate_content_stream(prompt):
        yield chunk.text
```

#### Challenge 4: Bahasa Indonesia NLP

**Problem**: Embedding model trained mostly on English data.

**Solution**:
```python
# 1. Use multilingual embedding model
embeddings = HuggingFaceEmbeddings(
    model_name="sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2",
    # Supports 50+ languages including Indonesian
)

# 2. Keyword-based fallback for important terms
INDONESIAN_KEYWORDS = {
    "stok": ["stock", "inventory"],
    "gudang": ["warehouse", "storage"],
    "penjualan": ["sales", "transaction"],
    "keuntungan": ["profit", "margin"],
}

def enhance_query(question: str) -> str:
    """Add English synonyms to improve retrieval"""
    for indo, eng_list in INDONESIAN_KEYWORDS.items():
        if indo in question.lower():
            question += " " + " ".join(eng_list)
    return question
```

#### Challenge 5: Knowledge Base Updates

**Problem**: Manual file berubah, tapi index tidak otomatis update.

**Solution**:
```python
# Automatic hash-based rebuild detection
def should_rebuild_index() -> Tuple[bool, str]:
    metadata = load_index_metadata()
    
    # Check file hash
    current_hash = get_manual_file_hash()
    if current_hash != metadata.get("file_hash", ""):
        return True, "Manual file has changed"
    
    # Check index exists
    if not (settings.chroma_persist_dir / "chroma.sqlite3").exists():
        return True, "Index file missing"
    
    return False, "Index up to date"

# Called on startup
vectorstore = get_vectorstore()  # Auto-rebuilds if needed
```

### 15.2 Lessons Learned

1. **Start with simple routing**: Keyword-based routing lebih reliable daripada pure LLM routing untuk domain-specific questions.

2. **Cache aggressively**: Database queries dan embeddings bisa di-cache untuk mengurangi latency.

3. **Fallback chains**: Selalu sediakan fallback (e.g., keyword search jika vector search gagal).

4. **User feedback loop**: Log semua pertanyaan dan jawaban untuk improvement.

5. **Cost monitoring**: LLM API calls expensive, implement rate limiting dan usage tracking.

---

## Lampiran

### A. Daftar Pertanyaan RAGAS Evaluation Dataset

Total: 51 pertanyaan evaluasi mencakup 8 kategori:
1. Factoid (10) - Pertanyaan fakta langsung
2. Analytical (10) - Analisis data
3. Comparative (8) - Perbandingan
4. Multi-hop (7) - Pertanyaan multi-langkah
5. Exploratory (5) - Eksplorasi data
6. Aggregation (5) - Agregasi data
7. Temporal (3) - Berbasis waktu
8. Recommendation (3) - Rekomendasi

### B. Konfigurasi Sistem

```python
# ai_service/core/config.py
from pydantic_settings import BaseSettings
from pathlib import Path

class Settings(BaseSettings):
    # API Keys
    gemini_api_key: str
    
    # Model Configuration
    gemini_model: str = "gemini-2.0-flash"
    embedding_model: str = "models/text-embedding-004"
    temperature: float = 0.3
    
    # Database
    database_url: str
    
    # Vector Store
    chroma_persist_dir: Path = Path("./manual_index")
    chunk_size: int = 1000
    chunk_overlap: int = 200
    
    # RAG Pipeline
    max_retries: int = 3
    
    # Market Basket Analysis
    mba_min_support: float = 0.01
    mba_min_confidence: float = 0.3
    mba_days_lookback: int = 90
    
    # Knowledge Base
    manual_path: Path = Path("./siwur_complete_manual.txt")
    
    class Config:
        env_file = ".env"
```

### C. Environment Variables

```bash
# .env.example
GEMINI_API_KEY=your_gemini_api_key_here
DATABASE_URL=mysql+pymysql://user:password@localhost:3306/siwur
OPENAI_API_KEY=optional_fallback_key
```

### D. API Rate Limits

| Endpoint | Rate Limit | Burst |
|----------|------------|-------|
| POST /chat | 60/minute | 10 |
| POST /mba | 10/minute | 2 |
| GET /health | Unlimited | - |

### E. Referensi

1. Lewis, P., et al. (2020). "Retrieval-Augmented Generation for Knowledge-Intensive NLP Tasks". NeurIPS 2020.
2. Agrawal, R., & Srikant, R. (1994). "Fast Algorithms for Mining Association Rules". VLDB 1994.
3. LangChain Documentation - https://python.langchain.com/
4. Google Gemini API Documentation - https://ai.google.dev/
5. ChromaDB Documentation - https://docs.trychroma.com/
6. FastAPI Documentation - https://fastapi.tiangolo.com/
7. Laravel Livewire Documentation - https://livewire.laravel.com/

---

*Dokumen ini dibuat sebagai bagian dari Skripsi: Implementasi Chatbot RAG pada Sistem SIWUR*

*Versi: 2.0 | Tanggal: Desember 2025*
