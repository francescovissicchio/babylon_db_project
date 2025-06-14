from fastapi import FastAPI
from pydantic import BaseModel
import requests

app = FastAPI()

class SintomiInput(BaseModel):
    sintomi: str

@app.post("/deduci")
def deduci_specializzazione(input: SintomiInput):
    prompt = f"""
    Sei un assistente medico. Il paziente dice: "{input.sintomi}".
    Quale tra queste specializzazioni è la più adatta?
    - Cardiologia
    - Pneumologia
    - Dermatologia
    - Neurologia
    - Gastroenterologia
    - Oftalmologia
    - Medicina generale
    - Psichiatria
    - Ginecologia
    - Oftalmologia
    - Endocrinologia
    - Urologia
    Rispondi solo con il nome della specializzazione.
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

    return {"specializzazione": testo}

