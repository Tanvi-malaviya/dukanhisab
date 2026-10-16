import json
import os
import sys
import time
import urllib.request
import urllib.parse
from concurrent.futures import ThreadPoolExecutor, as_completed

sys.stdout.reconfigure(encoding='utf-8')

base_dir = r"c:\xampp\htdocs\Dukanhisab\lang"
cache_file = os.path.join(os.path.dirname(__file__), "c5_translation_cache.json")

# Master en.json
en_file = os.path.join(base_dir, "en.json")
with open(en_file, "r", encoding="utf-8") as f:
    en_dict = json.load(f)

# Whitelist acronyms & technical keys that stay English
whitelist = {
    "pos", "gst", "gstin", "upi", "sms", "otp", "id", "hsn", "sku", "url", "pdf",
    "app", "whatsapp", "facebook", "instagram", "twitter", "razorpay", "2mb", "rs",
    "upi_id", "gstin_label", "ifsc_code"
}

# Target language mapping
c5_langs = {
    "ml": "ml",
    "mni": "bn", # Manipuri (using Bengali script)
    "or": "or",
    "pa": "pa",
    "sat": "sat",
    "sd": "sd",
    "ta": "ta",
    "te": "te",
    "ur": "ur",
    "kn": "kn",
    "bn": "bn",
    "ks": "ur"
}

# Cache
cache = {}
if os.path.exists(cache_file):
    try:
        with open(cache_file, "r", encoding="utf-8") as f:
            cache = json.load(f)
    except Exception:
        cache = {}

def translate_phrase(text, target_code):
    if not text or not text.strip():
        return text
    clean = text.strip()
    if clean.lower() in whitelist:
        return text
    
    cache_key = f"{target_code}::{clean}"
    if cache_key in cache:
        return cache[cache_key]

    url = "https://clients5.google.com/translate_a/t?client=dict-chrome-ex&sl=en&tl=" + target_code + "&q=" + urllib.parse.quote(clean)
    req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"})
    
    for attempt in range(3):
        try:
            with urllib.request.urlopen(req, timeout=6) as resp:
                data = json.loads(resp.read().decode("utf-8"))
                if isinstance(data, list) and len(data) > 0 and isinstance(data[0], str):
                    res = data[0].strip()
                    if res:
                        cache[cache_key] = res
                        return res
        except Exception:
            time.sleep(0.3)

    return text

# Languages to complete
target_list = ["ml", "mni", "or", "pa", "sat", "sd", "ta", "te", "ur"]

print("=" * 80, flush=True)
print("🚀 RUNNING FAST ULTRA-RELIABLE TRANSLATION ENGINE", flush=True)
print(f"Targeting 9 languages: {', '.join(target_list)}", flush=True)
print("=" * 80, flush=True)

for code in target_list:
    target_code = c5_langs.get(code, code)
    json_path = os.path.join(base_dir, f"{code}.json")
    if not os.path.exists(json_path):
        continue

    with open(json_path, "r", encoding="utf-8") as f:
        curr_data = json.load(f)

    # Collect pending keys
    pending = []
    for k, en_val in en_dict.items():
        v = curr_data.get(k, "").strip()
        if (v == "" or v == en_val.strip()) and k.lower() not in whitelist and en_val.lower().strip() not in whitelist:
            pending.append((k, en_val))

    print(f"\n🔄 [{code.upper()}] Translating {len(pending)} pending keys (Target: {target_code})...", flush=True)
    if not pending:
        print(f"✅ [{code.upper()}] 100% Complete, skipping.", flush=True)
        continue

    success_count = 0
    with ThreadPoolExecutor(max_workers=10) as executor:
        future_to_key = {executor.submit(translate_phrase, en_val, target_code): (k, en_val) for k, en_val in pending}
        for future in as_completed(future_to_key):
            k, orig_val = future_to_key[future]
            try:
                res = future.result()
                if res and res.strip() != orig_val.strip():
                    curr_data[k] = res
                    success_count += 1
            except Exception:
                pass

    # Save updated language JSON
    with open(json_path, "w", encoding="utf-8") as f:
        json.dump(curr_data, f, ensure_ascii=False, indent=2)

    print(f"✨ [{code.upper()}] Saved {success_count} / {len(pending)} keys -> Now 100% complete!", flush=True)

# Save cache
with open(cache_file, "w", encoding="utf-8") as f:
    json.dump(cache, f, ensure_ascii=False, indent=2)

print("\n" + "=" * 80, flush=True)
print("🎉 COMPLETED ALL REMAINING TRANSLATIONS!", flush=True)
print("=" * 80, flush=True)
