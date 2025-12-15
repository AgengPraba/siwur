# Skill: RAGAS Evaluation

## Deskripsi
Skill ini menjelaskan cara mengevaluasi RAG chatbot SIWUR menggunakan framework RAGAS (Retrieval Augmented Generation Assessment).

## Apa itu RAGAS?

RAGAS adalah framework evaluasi untuk sistem RAG yang mengukur:
1. **Faithfulness** - Apakah jawaban berdasarkan konteks yang di-retrieve?
2. **Answer Relevancy** - Apakah jawaban relevan dengan pertanyaan?
3. **Context Precision** - Apakah konteks yang di-retrieve tepat?
4. **Context Recall** - Apakah konteks mencakup semua informasi yang dibutuhkan?

## Dataset Evaluasi

### Lokasi Dataset
```
ai_service/ragas_evaluation_dataset.csv
```

### Struktur Dataset
```csv
question,ground_truth,category,difficulty
```

- **question**: Pertanyaan yang akan diuji
- **ground_truth**: Jawaban yang diharapkan
- **category**: Jenis pertanyaan (factoid, analytical, comparative, dll)
- **difficulty**: Tingkat kesulitan (easy, medium, hard)

### Kategori Pertanyaan

1. **factoid** - Pertanyaan faktual sederhana
   - "Berapa stok Indomie Goreng saat ini?"
   
2. **analytical** - Pertanyaan analisis
   - "Produk apa yang paling laku?"
   
3. **comparative** - Pertanyaan perbandingan
   - "Mana yang lebih laku, Indomie atau Mie Sedaap?"
   
4. **multi_hop** - Pertanyaan multi-langkah
   - "Jika saya restock mie, berapa total biayanya?"
   
5. **exploratory** - Pertanyaan eksploratif
   - "Daftar semua supplier yang terdaftar"
   
6. **aggregation** - Pertanyaan agregasi
   - "Berapa total item barang di toko?"
   
7. **temporal** - Pertanyaan waktu
   - "Bagaimana performa penjualan bulan lalu?"
   
8. **recommendation** - Pertanyaan rekomendasi
   - "Produk apa yang cocok untuk bundling?"

## Setup RAGAS

### Requirements
```txt
ragas>=0.1.0
langchain>=0.1.0
langchain-google-genai>=1.0.0
datasets>=2.0.0
pandas>=2.0.0
```

### Installation
```bash
pip install ragas langchain-google-genai datasets pandas
```

## Implementation

### 1. Load Dataset
```python
import pandas as pd
from datasets import Dataset

def load_evaluation_dataset(csv_path: str) -> Dataset:
    """Load dataset evaluasi dari CSV"""
    df = pd.read_csv(csv_path)
    
    # Konversi ke Hugging Face Dataset format
    data = {
        "question": df["question"].tolist(),
        "ground_truth": df["ground_truth"].tolist(),
        "category": df["category"].tolist(),
        "difficulty": df["difficulty"].tolist(),
    }
    
    return Dataset.from_dict(data)
```

### 2. Generate Responses
```python
from typing import List, Dict

def generate_responses(
    questions: List[str],
    rag_pipeline,
    toko_id: int
) -> List[Dict]:
    """Generate jawaban dari RAG pipeline"""
    
    results = []
    
    for question in questions:
        # Run RAG pipeline
        result = rag_pipeline.run(question, toko_id)
        
        results.append({
            "question": question,
            "answer": result["generation"],
            "contexts": [doc.page_content for doc in result["documents"]],
        })
    
    return results
```

### 3. Prepare RAGAS Dataset
```python
from datasets import Dataset

def prepare_ragas_dataset(
    responses: List[Dict],
    ground_truths: List[str]
) -> Dataset:
    """Prepare dataset untuk RAGAS evaluation"""
    
    data = {
        "question": [r["question"] for r in responses],
        "answer": [r["answer"] for r in responses],
        "contexts": [r["contexts"] for r in responses],
        "ground_truth": ground_truths,
    }
    
    return Dataset.from_dict(data)
```

### 4. Run RAGAS Evaluation
```python
from ragas import evaluate
from ragas.metrics import (
    faithfulness,
    answer_relevancy,
    context_precision,
    context_recall,
)
from langchain_google_genai import ChatGoogleGenerativeAI

def run_ragas_evaluation(dataset: Dataset, gemini_api_key: str):
    """Run RAGAS evaluation dengan Gemini"""
    
    # Setup LLM untuk evaluasi
    llm = ChatGoogleGenerativeAI(
        model="gemini-1.5-flash",
        google_api_key=gemini_api_key,
        temperature=0,
    )
    
    # Run evaluation
    result = evaluate(
        dataset,
        metrics=[
            faithfulness,
            answer_relevancy,
            context_precision,
            context_recall,
        ],
        llm=llm,
    )
    
    return result
```

### 5. Full Evaluation Script
```python
import os
import pandas as pd
from datetime import datetime

def run_full_evaluation(
    csv_path: str,
    rag_pipeline,
    toko_id: int,
    output_dir: str = "evaluation_results"
):
    """Run full RAGAS evaluation"""
    
    os.makedirs(output_dir, exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    
    # Load dataset
    print("Loading evaluation dataset...")
    df = pd.read_csv(csv_path)
    questions = df["question"].tolist()
    ground_truths = df["ground_truth"].tolist()
    categories = df["category"].tolist()
    difficulties = df["difficulty"].tolist()
    
    # Generate responses
    print(f"Generating responses for {len(questions)} questions...")
    responses = generate_responses(questions, rag_pipeline, toko_id)
    
    # Prepare RAGAS dataset
    ragas_dataset = prepare_ragas_dataset(responses, ground_truths)
    
    # Run evaluation
    print("Running RAGAS evaluation...")
    result = run_ragas_evaluation(
        ragas_dataset,
        os.getenv("GEMINI_API_KEY")
    )
    
    # Save results
    result_df = result.to_pandas()
    result_df["category"] = categories
    result_df["difficulty"] = difficulties
    
    output_path = f"{output_dir}/ragas_results_{timestamp}.csv"
    result_df.to_csv(output_path, index=False)
    
    # Print summary
    print("\n=== RAGAS Evaluation Summary ===")
    print(f"Total questions: {len(questions)}")
    print(f"Faithfulness: {result['faithfulness']:.4f}")
    print(f"Answer Relevancy: {result['answer_relevancy']:.4f}")
    print(f"Context Precision: {result['context_precision']:.4f}")
    print(f"Context Recall: {result['context_recall']:.4f}")
    
    # Summary by category
    print("\n=== By Category ===")
    for cat in result_df["category"].unique():
        cat_df = result_df[result_df["category"] == cat]
        print(f"\n{cat.upper()}:")
        print(f"  Faithfulness: {cat_df['faithfulness'].mean():.4f}")
        print(f"  Answer Relevancy: {cat_df['answer_relevancy'].mean():.4f}")
    
    return result, result_df
```

## Metrics Interpretation

### Faithfulness (0-1)
- **> 0.9**: Excellent - Jawaban sangat berdasarkan konteks
- **0.7-0.9**: Good - Kebanyakan berdasarkan konteks
- **0.5-0.7**: Fair - Beberapa hallucination
- **< 0.5**: Poor - Banyak hallucination

### Answer Relevancy (0-1)
- **> 0.9**: Excellent - Jawaban sangat relevan
- **0.7-0.9**: Good - Jawaban relevan
- **0.5-0.7**: Fair - Sebagian relevan
- **< 0.5**: Poor - Tidak relevan

### Context Precision (0-1)
- **> 0.9**: Excellent - Konteks sangat tepat
- **0.7-0.9**: Good - Konteks tepat
- **0.5-0.7**: Fair - Beberapa konteks tidak tepat
- **< 0.5**: Poor - Konteks tidak tepat

### Context Recall (0-1)
- **> 0.9**: Excellent - Semua info ter-retrieve
- **0.7-0.9**: Good - Sebagian besar ter-retrieve
- **0.5-0.7**: Fair - Beberapa info missing
- **< 0.5**: Poor - Banyak info missing

## Custom Metrics untuk SIWUR

### SQL Accuracy (untuk database queries)
```python
def sql_accuracy_metric(response: str, expected: str) -> float:
    """Metric khusus untuk akurasi data dari database"""
    # Extract numbers from response
    response_numbers = extract_numbers(response)
    expected_numbers = extract_numbers(expected)
    
    if not expected_numbers:
        return 1.0 if response_numbers == expected_numbers else 0.0
    
    correct = sum(1 for n in response_numbers if n in expected_numbers)
    return correct / len(expected_numbers)
```

### Action Correctness (untuk panduan)
```python
def action_correctness_metric(response: str, expected_steps: List[str]) -> float:
    """Metric untuk akurasi langkah-langkah"""
    response_lower = response.lower()
    
    correct_steps = sum(
        1 for step in expected_steps 
        if step.lower() in response_lower
    )
    
    return correct_steps / len(expected_steps)
```

## Continuous Evaluation

### Automated Testing
```python
# tests/test_rag_evaluation.py
import pytest

def test_faithfulness_threshold():
    result = run_ragas_evaluation(test_dataset)
    assert result['faithfulness'] >= 0.7, "Faithfulness below threshold"

def test_answer_relevancy_threshold():
    result = run_ragas_evaluation(test_dataset)
    assert result['answer_relevancy'] >= 0.7, "Answer relevancy below threshold"
```

### CI/CD Integration
```yaml
# .github/workflows/rag_evaluation.yml
name: RAG Evaluation

on:
  push:
    paths:
      - 'ai_service/**'

jobs:
  evaluate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup Python
        uses: actions/setup-python@v2
        with:
          python-version: '3.11'
      
      - name: Install dependencies
        run: pip install -r ai_service/requirements.txt
      
      - name: Run RAGAS evaluation
        env:
          GEMINI_API_KEY: ${{ secrets.GEMINI_API_KEY }}
        run: python ai_service/run_evaluation.py
```

## Best Practices

1. **Regular evaluation** - Jalankan evaluasi setelah setiap perubahan
2. **Diverse test set** - Gunakan berbagai kategori pertanyaan
3. **Track metrics over time** - Monitor trend metrics
4. **Human evaluation** - Combine dengan human review untuk kualitas
5. **A/B testing** - Bandingkan versi RAG yang berbeda
