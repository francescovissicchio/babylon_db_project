from fastapi import FastAPI
from pydantic import BaseModel
import requests

app = FastAPI()

class SintomiInput(BaseModel):
    sintomi: str

@app.post("/deduci")
def deduci_specializzazione(input: SintomiInput):
    prompt = f"""
Sei un assistente medico AI. Il paziente riferisce: "{input.sintomi}".

Tra le seguenti specializzazioni, indica SOLO quella PIÙ adatta alla valutazione dei sintomi del paziente.
Se il sintomo è generico o non specifico, scegli "Medicina generale", altrimenti scegli la specializzazione più specifica possibile.

Rispondi solo con uno dei seguenti nomi, senza aggiungere altro:
- Cardiologia
- Pneumologia
- Dermatologia
- Neurologia
- Gastroenterologia
- Oftalmologia
- Medicina generale
- Psichiatria
- Ginecologia
- Endocrinologia
- Urologia
"""

    response = requests.post(
        "http://localhost:11434/api/generate",
        json={
            "model": "mistral",
            "prompt": prompt,
            "stream": False
        }
    )

    if response.status_code != 200:
        return {"specializzazione": "Medicina generale"}

    risultato = response.json()
    testo = risultato["response"].strip()

    # Prendi solo la prima riga, elimina eventuali extra o testi "strani"
    testo = testo.split("\n")[0].strip()
    # Normalizza per evitare errori tipo "Specializzazione: Neurologia"
    for spec in [
        "Cardiologia","Pneumologia","Dermatologia","Neurologia","Gastroenterologia",
        "Oftalmologia","Medicina generale","Psichiatria","Ginecologia","Endocrinologia","Urologia"
    ]:
        if spec.lower() in testo.lower():
            testo = spec
            break
    else:
        testo = "Medicina generale"

    return {"specializzazione": testo}


