from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
import time
from dataclasses import dataclass, field
import sys
import os
from dotenv import load_dotenv

load_dotenv(".env/.env")
chromedriver_path = os.getenv("CHROMEDRIVER_PATH")

# Set up Selenium options
options = Options()
options.add_argument("--headless") 
options.add_argument("--disable-gpu")
options.add_argument("--no-sandbox")
options.add_argument("--disable-blink-features=AutomationControlled")
options.add_argument("--disable-usb-discovery") 
options.add_argument("user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36")

service = Service(chromedriver_path)
driver = webdriver.Chrome(service=service, options=options)

ACTIVE_SUBSCRIPTIONS = ["Amazon", "Netflix", "Hulu", "Disneyplus"] #"Play"

@dataclass
class Movie:
    title: str = ""
    description: str = ""
    tomato: str = ""
    imdb: str = ""
    image: str = ""
    trailer: str = ""
    rating: str = ""
    year: str = ""
    genre: str = ""
    runtime: str = ""
    services: str = ""
    releaseDate: str = ""
    
def get_movie_info(search):
    
    try:
        searchUrl = search if search.startswith("http") else f"https://www.google.com/search?q={search}"
        driver.get(searchUrl)
        time.sleep(1)
        
        movie = Movie()
        # movie.title = get_title(content)
        # movie.description = get_description(content)
        movie.imdb, movie.tomato = get_scores()
        # movie.release_date = get_release_date(content)
        # movie.rating, movie.year, movie.genre, movie.runtime = get_meta_info(content)
        # movie.trailer = get_trailer()
        # movie.services = get_services(content)
        # movie.image = get_image(search)
        
    finally:
        driver.quit()
        return movie
    
def get_scores():
    try:
        # IMDb Score
        imdb_score = driver.find_element(By.XPATH, "//span[@title='IMDb' and @aria-hidden='true']/preceding-sibling::span").text
        imdb_score = imdb_score.replace("IMDb", "").replace("/10", "").strip()

    except Exception as e:
        print(f"Error occurred: {e}")
        imdb_score = ""

    try:
        # Rotten Tomatoes Score
        tomatoes_score = driver.find_element(By.XPATH, "//span[@title='Rotten Tomatoes' and @aria-hidden='true']/preceding-sibling::span").text
        tomatoes_score = tomatoes_score.replace("Rotten Tomatoes", "").strip()

    except Exception as e:
        print(f"Error occurred: {e}")
        tomatoes_score = ""
    
    return imdb_score, tomatoes_score

# test = "interstellar"
test = "inception"

search = sys.argv[1] if len(sys.argv) > 1 else test
print(get_movie_info(search))
