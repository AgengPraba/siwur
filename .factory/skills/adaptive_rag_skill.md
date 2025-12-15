# Skill: Adaptive RAG Implementation

## Deskripsi
Skill ini menjelaskan implementasi Adaptive RAG untuk chatbot SIWUR menggunakan teknik dari LangGraph tutorial, diadaptasi untuk Gemini API.

## Konsep Adaptive RAG

Adaptive RAG menggabungkan:
1. **Query Analysis/Routing** - Menentukan sumber data yang tepat
2. **Self-Corrective RAG** - Evaluasi dan perbaikan hasil retrieval
3. **Multi-Source Retrieval** - Database dinamis + Manual book

## Arsitektur Flow

```
┌─────────────┐
│   Question  │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│  Route Question │ ─── Classify: database/manual/hybrid
└───────┬─────────┘
        │
   ┌────┴────┐
   ▼         ▼
┌──────┐  ┌────────┐
│  DB  │  │ Manual │
│Query │  │ Search │
└──┬───┘  └───┬────┘
   │          │
   └────┬─────┘
        ▼
┌─────────────────┐
│ Grade Documents │ ─── Relevance check
└───────┬─────────┘
        │
   ┌────┴────┐
   ▼         ▼
  Yes       No
   │         │
   │    ┌────┴────┐
   │    │Transform│ ─── Rewrite query
   │    │  Query  │
   │    └────┬────┘
   │         │
   │         ▼
   │    (Retry Retrieve)
   │
   ▼
┌─────────────────┐
│    Generate     │
└───────┬─────────┘
        │
        ▼
┌─────────────────┐
│ Check Hallucin. │ ─── Grounded in facts?
└───────┬─────────┘
        │
   ┌────┴────┐
   ▼         ▼
  Yes       No
   │         │
   ▼    (Retry Generate)
┌─────────────────┐
│  Grade Answer   │ ─── Answers question?
└───────┬─────────┘
        │
   ┌────┴────┐
   ▼         ▼
  Yes       No
   │         │
   ▼    (Transform Query)
┌─────────────────┐
│  Final Answer   │
└─────────────────┘
```

## Komponen Utama

### 1. Question Router
Menentukan datasource yang tepat berdasarkan pertanyaan:

```python
from pydantic import BaseModel, Field
from typing import Literal

class RouteQuery(BaseModel):
    """Route query ke datasource yang tepat"""
    datasource: Literal["database", "manual", "hybrid"] = Field(
        description="Pilih datasource: database untuk data real-time toko, manual untuk panduan sistem, hybrid untuk keduanya"
    )
    reasoning: str = Field(description="Alasan pemilihan datasource")

ROUTE_PROMPT = """Anda adalah router yang menentukan datasource untuk menjawab pertanyaan tentang sistem SIWUR.

Datasource yang tersedia:
1. database - Data real-time toko: stok barang, transaksi, customer, supplier, dll
2. manual - Panduan penggunaan sistem: cara menggunakan fitur, alur kerja, izin akses
3. hybrid - Kombinasi keduanya untuk pertanyaan yang membutuhkan data dan panduan

Contoh routing:
- "Berapa stok Indomie?" → database (data stok real-time)
- "Bagaimana cara membuat penjualan?" → manual (panduan sistem)
- "Produk apa yang laris dan bagaimana cara restock?" → hybrid

Pertanyaan: {question}
"""
```

### 2. Retrieval Grader
Menilai relevansi dokumen yang di-retrieve:

```python
class GradeDocuments(BaseModel):
    """Skor biner untuk relevansi dokumen"""
    binary_score: Literal["yes", "no"] = Field(
        description="Dokumen relevan dengan pertanyaan: 'yes' atau 'no'"
    )
    explanation: str = Field(description="Penjelasan singkat")

GRADE_PROMPT = """Anda adalah grader yang menilai relevansi dokumen dengan pertanyaan.

Jika dokumen berisi kata kunci atau makna semantik yang terkait dengan pertanyaan, nilai sebagai relevan.
Tujuannya adalah memfilter hasil retrieval yang tidak relevan.

Dokumen: {document}
Pertanyaan: {question}
"""
```

### 3. Hallucination Grader
Memeriksa apakah jawaban berdasarkan fakta:

```python
class GradeHallucinations(BaseModel):
    """Skor biner untuk hallucination"""
    binary_score: Literal["yes", "no"] = Field(
        description="Jawaban berdasarkan fakta: 'yes' atau 'no'"
    )
    explanation: str = Field(description="Penjelasan singkat")

HALLUCINATION_PROMPT = """Anda adalah grader yang menilai apakah jawaban LLM berdasarkan fakta yang di-retrieve.

Berikan skor 'yes' jika jawaban berdasarkan fakta, 'no' jika tidak.

Fakta: {documents}
Jawaban LLM: {generation}
"""
```

### 4. Answer Grader
Menilai apakah jawaban menjawab pertanyaan:

```python
class GradeAnswer(BaseModel):
    """Skor biner untuk kualitas jawaban"""
    binary_score: Literal["yes", "no"] = Field(
        description="Jawaban menjawab pertanyaan: 'yes' atau 'no'"
    )
    explanation: str = Field(description="Penjelasan singkat")

ANSWER_PROMPT = """Anda adalah grader yang menilai apakah jawaban menjawab pertanyaan.

Berikan skor 'yes' jika jawaban menjawab pertanyaan, 'no' jika tidak.

Pertanyaan: {question}
Jawaban: {generation}
"""
```

### 5. Question Rewriter
Menulis ulang pertanyaan untuk retrieval yang lebih baik:

```python
REWRITE_PROMPT = """Anda adalah penulis ulang pertanyaan yang mengoptimalkan pertanyaan untuk pencarian vector.

Lihat pertanyaan input dan coba pahami maksud semantiknya.
Tulis ulang pertanyaan yang lebih baik untuk retrieval.

Pertanyaan awal: {question}
Pertanyaan yang diperbaiki:
"""
```

## Graph State

```python
from typing import List, TypedDict
from langchain_core.documents import Document

class GraphState(TypedDict):
    """State untuk adaptive RAG graph"""
    question: str              # Pertanyaan user
    generation: str            # Jawaban yang di-generate
    documents: List[Document]  # Dokumen yang di-retrieve
    datasource: str            # database/manual/hybrid
    toko_id: int              # ID toko untuk filter data
    retry_count: int          # Counter untuk mencegah infinite loop
```

## Implementation dengan Gemini

```python
import google.generativeai as genai
from typing import Any

class AdaptiveRAG:
    def __init__(self, gemini_api_key: str):
        genai.configure(api_key=gemini_api_key)
        self.model = genai.GenerativeModel('gemini-1.5-flash')
        
    def route_question(self, question: str) -> RouteQuery:
        """Route pertanyaan ke datasource yang tepat"""
        response = self.model.generate_content(
            ROUTE_PROMPT.format(question=question),
            generation_config=genai.GenerationConfig(
                response_mime_type="application/json",
                response_schema=RouteQuery
            )
        )
        return RouteQuery.model_validate_json(response.text)
    
    def grade_documents(self, question: str, document: str) -> GradeDocuments:
        """Grade relevansi dokumen"""
        response = self.model.generate_content(
            GRADE_PROMPT.format(question=question, document=document),
            generation_config=genai.GenerationConfig(
                response_mime_type="application/json",
                response_schema=GradeDocuments
            )
        )
        return GradeDocuments.model_validate_json(response.text)
    
    def check_hallucination(self, documents: str, generation: str) -> GradeHallucinations:
        """Check hallucination dalam jawaban"""
        response = self.model.generate_content(
            HALLUCINATION_PROMPT.format(documents=documents, generation=generation),
            generation_config=genai.GenerationConfig(
                response_mime_type="application/json",
                response_schema=GradeHallucinations
            )
        )
        return GradeHallucinations.model_validate_json(response.text)
    
    def grade_answer(self, question: str, generation: str) -> GradeAnswer:
        """Grade kualitas jawaban"""
        response = self.model.generate_content(
            ANSWER_PROMPT.format(question=question, generation=generation),
            generation_config=genai.GenerationConfig(
                response_mime_type="application/json",
                response_schema=GradeAnswer
            )
        )
        return GradeAnswer.model_validate_json(response.text)
    
    def rewrite_question(self, question: str) -> str:
        """Rewrite pertanyaan untuk retrieval yang lebih baik"""
        response = self.model.generate_content(
            REWRITE_PROMPT.format(question=question)
        )
        return response.text.strip()
    
    def generate_answer(self, question: str, context: str) -> str:
        """Generate jawaban dari konteks"""
        prompt = f"""Jawab pertanyaan berdasarkan konteks berikut.
        
Konteks:
{context}

Pertanyaan: {question}

Jawaban:"""
        response = self.model.generate_content(prompt)
        return response.text.strip()
```

## Flow Control dengan Max Retries

```python
MAX_RETRIES = 3

def run_adaptive_rag(state: GraphState) -> GraphState:
    """Main flow dengan retry limit"""
    
    # Route question
    route = route_question(state["question"])
    state["datasource"] = route.datasource
    
    # Retrieve documents
    state["documents"] = retrieve(state)
    
    # Grade documents
    filtered_docs = []
    for doc in state["documents"]:
        grade = grade_documents(state["question"], doc.page_content)
        if grade.binary_score == "yes":
            filtered_docs.append(doc)
    
    state["documents"] = filtered_docs
    
    # If no relevant docs, rewrite query
    if not filtered_docs and state["retry_count"] < MAX_RETRIES:
        state["question"] = rewrite_question(state["question"])
        state["retry_count"] += 1
        return run_adaptive_rag(state)  # Retry
    
    # Generate answer
    context = "\n\n".join([doc.page_content for doc in state["documents"]])
    state["generation"] = generate_answer(state["question"], context)
    
    # Check hallucination
    hallucination = check_hallucination(context, state["generation"])
    if hallucination.binary_score == "no" and state["retry_count"] < MAX_RETRIES:
        state["retry_count"] += 1
        # Regenerate
        state["generation"] = generate_answer(state["question"], context)
    
    # Grade answer
    answer_grade = grade_answer(state["question"], state["generation"])
    if answer_grade.binary_score == "no" and state["retry_count"] < MAX_RETRIES:
        state["question"] = rewrite_question(state["question"])
        state["retry_count"] += 1
        return run_adaptive_rag(state)  # Retry with rewritten question
    
    return state
```

## Best Practices

1. **Set max retries** - Hindari infinite loop dengan retry limit
2. **Cache embeddings** - Simpan embeddings untuk dokumen statis seperti manual
3. **Lazy loading** - Load database context on-demand berdasarkan routing
4. **Structured output** - Gunakan Pydantic untuk response yang konsisten
5. **Logging** - Log setiap step untuk debugging dan improvement
