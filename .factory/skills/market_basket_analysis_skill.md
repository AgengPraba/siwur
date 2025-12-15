# Skill: Market Basket Analysis

## Deskripsi
Skill ini menjelaskan cara mengimplementasikan Market Basket Analysis (MBA) untuk chatbot SIWUR, menganalisis pola pembelian pelanggan dan mengidentifikasi produk yang sering dibeli bersamaan.

## Konsep Market Basket Analysis

Market Basket Analysis menggunakan algoritma **Association Rules** untuk menemukan:
1. **Frequent Itemsets** - Kombinasi produk yang sering muncul bersamaan
2. **Association Rules** - Aturan "Jika beli X, maka kemungkinan beli Y"

### Metrics Utama

1. **Support** - Seberapa sering itemset muncul dalam transaksi
   ```
   Support(A) = Transaksi mengandung A / Total transaksi
   Support(A,B) = Transaksi mengandung A dan B / Total transaksi
   ```

2. **Confidence** - Probabilitas B dibeli jika A dibeli
   ```
   Confidence(A→B) = Support(A,B) / Support(A)
   ```

3. **Lift** - Kekuatan asosiasi (>1 = positif, <1 = negatif)
   ```
   Lift(A→B) = Confidence(A→B) / Support(B)
   ```

## Query Database untuk MBA

### 1. Extract Transaction Data
```sql
-- Get all transactions with items (basket format)
SELECT 
    p.id as transaction_id,
    p.tanggal_penjualan,
    p.customer_id,
    c.nama_customer,
    GROUP_CONCAT(b.nama_barang SEPARATOR ', ') as items,
    COUNT(pd.barang_id) as item_count
FROM penjualan p
JOIN penjualan_detail pd ON p.id = pd.penjualan_id
JOIN barang b ON pd.barang_id = b.id
JOIN customer c ON p.customer_id = c.id
WHERE p.toko_id = :toko_id
    AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
GROUP BY p.id
HAVING item_count >= 2
ORDER BY p.tanggal_penjualan DESC;
```

### 2. Get Item Pairs Frequency
```sql
-- Frequent item pairs
SELECT 
    b1.nama_barang as item_a,
    b2.nama_barang as item_b,
    COUNT(*) as frequency
FROM penjualan_detail pd1
JOIN penjualan_detail pd2 ON pd1.penjualan_id = pd2.penjualan_id 
    AND pd1.barang_id < pd2.barang_id
JOIN penjualan p ON pd1.penjualan_id = p.id
JOIN barang b1 ON pd1.barang_id = b1.id
JOIN barang b2 ON pd2.barang_id = b2.id
WHERE p.toko_id = :toko_id
    AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
GROUP BY pd1.barang_id, pd2.barang_id
ORDER BY frequency DESC
LIMIT 20;
```

### 3. Calculate Support, Confidence, Lift
```sql
-- Full association rules calculation
WITH transaction_count AS (
    SELECT COUNT(DISTINCT id) as total
    FROM penjualan
    WHERE toko_id = :toko_id
        AND tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
),
item_support AS (
    SELECT 
        pd.barang_id,
        b.nama_barang,
        COUNT(DISTINCT pd.penjualan_id) as item_trans,
        COUNT(DISTINCT pd.penjualan_id) * 1.0 / (SELECT total FROM transaction_count) as support
    FROM penjualan_detail pd
    JOIN penjualan p ON pd.penjualan_id = p.id
    JOIN barang b ON pd.barang_id = b.id
    WHERE p.toko_id = :toko_id
        AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    GROUP BY pd.barang_id
),
pair_support AS (
    SELECT 
        pd1.barang_id as item_a_id,
        pd2.barang_id as item_b_id,
        COUNT(DISTINCT pd1.penjualan_id) as pair_trans
    FROM penjualan_detail pd1
    JOIN penjualan_detail pd2 ON pd1.penjualan_id = pd2.penjualan_id 
        AND pd1.barang_id < pd2.barang_id
    JOIN penjualan p ON pd1.penjualan_id = p.id
    WHERE p.toko_id = :toko_id
        AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    GROUP BY pd1.barang_id, pd2.barang_id
)
SELECT 
    ia.nama_barang as antecedent,
    ib.nama_barang as consequent,
    ps.pair_trans,
    ps.pair_trans * 1.0 / (SELECT total FROM transaction_count) as support,
    ps.pair_trans * 1.0 / ia.item_trans as confidence,
    (ps.pair_trans * 1.0 / ia.item_trans) / ib.support as lift
FROM pair_support ps
JOIN item_support ia ON ps.item_a_id = ia.barang_id
JOIN item_support ib ON ps.item_b_id = ib.barang_id
WHERE ps.pair_trans >= 5  -- minimum support count
ORDER BY lift DESC, confidence DESC
LIMIT 20;
```

## Python Implementation

### 1. Data Preparation
```python
import pandas as pd
from sqlalchemy import create_engine
from mlxtend.frequent_patterns import apriori, association_rules
from mlxtend.preprocessing import TransactionEncoder

class MarketBasketAnalyzer:
    def __init__(self, db_connection_string: str):
        self.engine = create_engine(db_connection_string)
    
    def get_transactions(self, toko_id: int, days: int = 90) -> pd.DataFrame:
        """Get transaction data from database"""
        query = """
        SELECT 
            p.id as transaction_id,
            b.nama_barang as item
        FROM penjualan p
        JOIN penjualan_detail pd ON p.id = pd.penjualan_id
        JOIN barang b ON pd.barang_id = b.id
        WHERE p.toko_id = %(toko_id)s
            AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL %(days)s DAY)
        """
        return pd.read_sql(query, self.engine, params={"toko_id": toko_id, "days": days})
    
    def prepare_basket(self, df: pd.DataFrame) -> pd.DataFrame:
        """Convert to basket format (one-hot encoded)"""
        # Group items by transaction
        baskets = df.groupby('transaction_id')['item'].apply(list).tolist()
        
        # One-hot encode
        te = TransactionEncoder()
        te_array = te.fit_transform(baskets)
        
        return pd.DataFrame(te_array, columns=te.columns_)
```

### 2. Apriori Algorithm
```python
def run_apriori(self, toko_id: int, min_support: float = 0.01) -> dict:
    """Run Apriori algorithm"""
    
    # Get and prepare data
    df = self.get_transactions(toko_id)
    basket_df = self.prepare_basket(df)
    
    # Find frequent itemsets
    frequent_itemsets = apriori(
        basket_df, 
        min_support=min_support, 
        use_colnames=True
    )
    
    # Generate association rules
    rules = association_rules(
        frequent_itemsets, 
        metric="lift", 
        min_threshold=1.0
    )
    
    # Sort by lift
    rules = rules.sort_values(['lift', 'confidence'], ascending=[False, False])
    
    return {
        "frequent_itemsets": frequent_itemsets,
        "rules": rules,
        "total_transactions": len(basket_df),
        "total_items": len(basket_df.columns)
    }
```

### 3. Generate Insights
```python
def get_insights(self, toko_id: int) -> dict:
    """Generate MBA insights for chatbot"""
    
    result = self.run_apriori(toko_id)
    rules = result["rules"]
    
    insights = {
        "top_pairs": [],
        "bundling_recommendations": [],
        "cross_sell_opportunities": [],
        "summary": ""
    }
    
    # Top product pairs
    top_rules = rules.head(10)
    for _, rule in top_rules.iterrows():
        antecedents = list(rule['antecedents'])
        consequents = list(rule['consequents'])
        
        insights["top_pairs"].append({
            "if_buy": antecedents,
            "then_buy": consequents,
            "confidence": round(rule['confidence'] * 100, 1),
            "lift": round(rule['lift'], 2),
            "support": round(rule['support'] * 100, 2)
        })
    
    # Bundling recommendations (high confidence + high lift)
    bundle_rules = rules[(rules['confidence'] >= 0.5) & (rules['lift'] >= 1.5)]
    for _, rule in bundle_rules.head(5).iterrows():
        items = list(rule['antecedents']) + list(rule['consequents'])
        insights["bundling_recommendations"].append({
            "items": items,
            "confidence": round(rule['confidence'] * 100, 1),
            "reason": f"Dibeli bersamaan {round(rule['confidence'] * 100)}% waktu"
        })
    
    # Cross-sell opportunities (high lift but lower support - hidden gems)
    crosssell = rules[(rules['lift'] >= 2.0) & (rules['support'] < 0.1)]
    for _, rule in crosssell.head(5).iterrows():
        insights["cross_sell_opportunities"].append({
            "trigger_product": list(rule['antecedents']),
            "suggest_product": list(rule['consequents']),
            "lift": round(rule['lift'], 2)
        })
    
    # Summary
    insights["summary"] = f"""
Analisis dari {result['total_transactions']} transaksi dengan {result['total_items']} produk unik:
- Ditemukan {len(rules)} aturan asosiasi
- Top pair: {insights['top_pairs'][0]['if_buy'][0]} → {insights['top_pairs'][0]['then_buy'][0]} (confidence {insights['top_pairs'][0]['confidence']}%)
- {len(bundle_rules)} kombinasi potensial untuk bundling
"""
    
    return insights
```

## Context Generation untuk RAG

### Format Context untuk LLM
```python
def format_mba_context(insights: dict, question: str) -> str:
    """Format MBA insights sebagai context untuk LLM"""
    
    context = """
## Hasil Analisis Market Basket (Pola Pembelian Pelanggan)

### Produk yang Sering Dibeli Bersamaan
"""
    
    for i, pair in enumerate(insights["top_pairs"][:5], 1):
        if_buy = ", ".join(pair["if_buy"])
        then_buy = ", ".join(pair["then_buy"])
        context += f"""
{i}. Jika membeli **{if_buy}**, kemungkinan juga membeli **{then_buy}**
   - Confidence: {pair['confidence']}% pelanggan melakukan ini
   - Lift: {pair['lift']} (kekuatan asosiasi)
"""
    
    context += """
### Rekomendasi Bundling
"""
    for bundle in insights["bundling_recommendations"]:
        items = ", ".join(bundle["items"])
        context += f"- **{items}**: {bundle['reason']}\n"
    
    context += """
### Peluang Cross-Selling
"""
    for cs in insights["cross_sell_opportunities"]:
        trigger = ", ".join(cs["trigger_product"])
        suggest = ", ".join(cs["suggest_product"])
        context += f"- Saat pelanggan beli {trigger}, tawarkan {suggest} (lift: {cs['lift']})\n"
    
    context += f"""
### Ringkasan
{insights['summary']}
"""
    
    return context
```

## Integration dengan Adaptive RAG

### Router Update
```python
class RouteQuery(BaseModel):
    datasource: Literal["database", "manual", "hybrid", "analytics"] = Field(
        description="""
        Pilih datasource:
        - database: data real-time (stok, transaksi)
        - manual: panduan penggunaan sistem
        - hybrid: kombinasi database dan manual
        - analytics: analisis data (MBA, trend, prediksi)
        """
    )

ANALYTICS_KEYWORDS = [
    "analisis", "pola", "pattern", "bundling", "bersamaan", 
    "cross-sell", "rekomendasi produk", "frequent", "sering dibeli",
    "kombinasi", "paket", "trend", "prediksi"
]
```

### Retrieve dengan MBA Context
```python
def retrieve_analytics_context(question: str, toko_id: int) -> str:
    """Retrieve context dengan MBA analysis"""
    
    analyzer = MarketBasketAnalyzer(DATABASE_URL)
    insights = analyzer.get_insights(toko_id)
    
    # Format context
    mba_context = format_mba_context(insights, question)
    
    # Add methodology explanation
    methodology = """
## Metodologi Market Basket Analysis

Market Basket Analysis menggunakan algoritma Apriori untuk menemukan pola pembelian:

1. **Support**: Persentase transaksi yang mengandung item
2. **Confidence**: Probabilitas item B dibeli jika item A dibeli
3. **Lift**: Kekuatan asosiasi (>1 = asosiasi positif)

Interpretasi:
- Lift > 1.5: Asosiasi kuat, cocok untuk bundling
- Confidence > 50%: Kemungkinan tinggi dibeli bersamaan
- Support > 5%: Kombinasi cukup populer
"""
    
    return mba_context + methodology
```

## Pertanyaan MBA yang Didukung

### Factual
- "Produk apa yang sering dibeli bersamaan?"
- "Apa kombinasi produk terpopuler?"

### Recommendation
- "Produk apa yang cocok untuk bundling?"
- "Rekomendasi cross-selling untuk Indomie?"

### Analytical
- "Bagaimana pola pembelian pelanggan?"
- "Produk apa yang bisa ditawarkan saat beli rokok?"

### Strategic
- "Bagaimana meningkatkan penjualan dengan bundling?"
- "Strategi penempatan produk berdasarkan pola beli?"

## Caching untuk Performance

```python
from functools import lru_cache
from datetime import datetime, timedelta

class CachedMBAAnalyzer:
    def __init__(self):
        self.cache = {}
        self.cache_duration = timedelta(hours=1)
    
    def get_insights_cached(self, toko_id: int) -> dict:
        """Get insights dengan caching"""
        cache_key = f"mba_{toko_id}"
        
        if cache_key in self.cache:
            cached_data, timestamp = self.cache[cache_key]
            if datetime.now() - timestamp < self.cache_duration:
                return cached_data
        
        # Generate fresh insights
        analyzer = MarketBasketAnalyzer(DATABASE_URL)
        insights = analyzer.get_insights(toko_id)
        
        # Cache result
        self.cache[cache_key] = (insights, datetime.now())
        
        return insights
```

## Best Practices

1. **Minimum transactions** - Butuh minimal 100+ transaksi untuk hasil bermakna
2. **Time window** - Gunakan 3-6 bulan data untuk pola stabil
3. **Filter noise** - Set minimum support untuk filter kombinasi jarang
4. **Cache results** - MBA computation mahal, cache hasil
5. **Explain metrics** - Selalu jelaskan confidence/lift ke user
6. **Actionable insights** - Fokus pada insight yang bisa dieksekusi
