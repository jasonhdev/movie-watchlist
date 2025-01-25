from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
import time
from dataclasses import dataclass, asdict
from datetime import datetime
import os
import sys
from dotenv import load_dotenv
import re
import json

load_dotenv(".env/.env")
chromedriver_path = os.getenv("CHROMEDRIVER_PATH")

options = Options()
options.add_argument("--headless") 
options.add_argument("--disable-gpu")
options.add_argument("--no-sandbox")
options.add_argument("--disable-blink-features=AutomationControlled")
options.add_argument("--disable-usb-discovery") 
options.add_argument("user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36")

service = Service(chromedriver_path)
driver = webdriver.Chrome(service=service, options=options)

ACTIVE_SUBSCRIPTIONS = [
    "Amazon Prime", 
    "Netflix",
    "Hulu",
    "Disney+",
    "HBO Max"
]

MEDIA_TYPES = [
    "movie", 
    "film", 
    "show", 
    "documentary", 
    "anime", 
    str(datetime.now().year)
]

MOVIE_DETECT_KEYWORDS = [
    "Release date",
    "Director",
    "Budget",
    "Cinematography",
    "Distributed by",
    "Genre",
    "Producers",
    "Network",
]

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
    
    def to_json(self):
        return json.dumps(asdict(self), indent=4)
    
def get_movie_info(search):
    try:
        set_driver_search_url(search)
        
        movie = Movie()
        movie.title = get_title()
        movie.services = get_services()
        movie.description = get_description() # Must be after get_services because of clicking issue
        movie.imdb, movie.tomato = get_scores()
        movie.releaseDate = get_release_date()
        movie.rating, movie.year, movie.genre, movie.runtime = get_meta_info()
        movie.trailer = get_trailer()
        movie.image = get_poster()
    except Exception as e:
        # print(f"Error occurred: {e}")
        pass
        
    finally:
        driver.quit()
        return movie.to_json()
    
def set_driver_search_url(search):
    if search.startswith("http"):
        searchUrl = search
        driver.get(searchUrl)
            
    else:
        searchUrl = f"https://www.google.com/search?q={search}"
        driver.get(searchUrl)
        
        potential_movie = driver.find_elements(By.XPATH, " | ".join([f"//*[contains(text(), '{word}')]" for word in MOVIE_DETECT_KEYWORDS]))
        time.sleep(1)
        
        if not potential_movie:
            for media_type in MEDIA_TYPES:
                searchUrl = f"https://www.google.com/search?q={search} {media_type}"
                driver.get(searchUrl)
                time.sleep(1)
                
                potential_movie = " | ".join([f"//*[contains(text(), '{word}')]" for word in MOVIE_DETECT_KEYWORDS])
                if potential_movie:
                    break
    
def get_title():
    try:
        title = driver.find_element(By.XPATH, "//div[@data-attrid='title']").text

    except Exception as e:
        # print(f"Error occurred: {e}")
        title = ""
        
    return title
    
def get_description():
    try:
        description_div = driver.find_element(By.XPATH, "//div[@data-attrid='description' or @data-attrid='VisualDigestDescription']")
        
        try:
            more_button = description_div.find_element(By.XPATH, ".//span[contains(@aria-label, 'More description')]")
            more_button.click()
        except Exception:
            pass

        description = description_div.text.replace("Description\n","")

    except Exception as e:
        # print(f"Error occurred: {e}")
        description = ""
        
    return description
    
def get_scores():
    try:
        # IMDb Score
        imdb_score = driver.find_element(By.XPATH, "//span[text()='IMDb' and @aria-hidden='true']/preceding-sibling::span").text
        imdb_score = imdb_score.replace("IMDb", "").replace("/10", "").strip()

    except Exception as e:
        # print(f"Error occurred: {e}")
        imdb_score = ""

    try:
        # Rotten Tomatoes Score
        tomatoes_score = driver.find_element(By.XPATH, "//span[text()='Rotten Tomatoes' and @aria-hidden='true']/preceding-sibling::span").text
        tomatoes_score = tomatoes_score.replace("Rotten Tomatoes", "").strip()

    except Exception as e:
        # print(f"Error occurred: {e}")
        tomatoes_score = ""
    
    return imdb_score, tomatoes_score
    
def get_trailer():
    try:
        trailer_url = driver.find_element(By.XPATH, "//a[contains(@href, 'youtube.com')]").get_attribute("href")
    except Exception as e:
        # print(f"Error occurred: {e}")
        trailer_url = ""
    
    return trailer_url

def get_meta_info():
    # Google result ordering: Rating, Year, Genre, Runtime
    rating, year, genre, runtime = "", "", "", ""
    possible_ratings = ['PG-13', 'PG', 'Not Rated', 'N/A', 'R', 'G']
    
    try:
        subtitle_element = driver.find_elements(By.XPATH, "//div[@data-attrid='subtitle']/span")
        meta_info = " ".join([span.text for span in subtitle_element]).split("‧")
        meta_info = [part.strip() for part in meta_info if part.strip() != ","]
    
        for info in meta_info:
            # Extract rating
            if not rating:
                rating = next((rating_value for rating_value in possible_ratings if rating_value in info), "").strip()
                info = info.replace(rating, '').strip() if rating else info

            # Match year to 4 digit integer
            if not year and len(info) == 4 and info.isnumeric():
                year = info
                continue
                
            # Match runtime (e.g. "2h 30m" or "2 seasons")
            if not runtime:
                runtime_match = re.search(r'(\d+\s*season[s]?)|((\d+)\s*h(?:\s*(\d+)\s*m)?)', info)
                if runtime_match:
                    runtime = runtime_match.group(0)
                    continue
            
            # Assign remaining value to genre
            if not genre:
                genre = info
                
    except Exception as e:
        # print(f"Error occurred: {e}")
        pass
    
    return rating, year, genre, runtime

def get_release_date():
    try:
        release_date = driver.find_element(By.XPATH, "//span[contains(text(), 'Release date')]/following-sibling::span").text
        release_date = re.sub(r"\((.*?)\)", "", release_date).replace("Release date:", "").replace("Initial release:", "").strip()
    
    except Exception as e:
        # print(f"Error occurred: {e}")
        release_date = ""
        
    return release_date

def get_services():
    service_clean_name = {
        "Amazon Prime Video": "Amazon Prime",
        "Max": "HBO Max",
    }

    available_service = ""
    
    try:
        where_watch_span = driver.find_element(By.XPATH, "//span[contains(text(), 'Where to watch')]")
        where_watch_span.click()
        time.sleep(1)
        
        where_watch_section = where_watch_span.find_element(By.XPATH, "./../../../following-sibling::div")
        where_watch_text = where_watch_section.text.split("\n")
        
        for i in range(len(where_watch_text) - 1):
            service_name = where_watch_text[i]
            if where_watch_text[i + 1] == "Subscription":
                service_name = service_clean_name.get(service_name, service_name)

                if service_name in ACTIVE_SUBSCRIPTIONS:
                    available_service = service_name
                    break
                
    except Exception as e:
        # print(f"Error occurred: {e}")
        pass
        
    return available_service

def get_poster():
    try:
        try:
            wiki_link = driver.find_element(By.XPATH, "//a[contains(@href, 'wikipedia.org')]")
            wiki_link.click()
            time.sleep(1)
        except Exception as e:
            pass

        poster_url = driver.find_element(By.XPATH, "//a[@class='mw-file-description']/img").get_attribute('src')

    except Exception as e:
        # print(f"Error occurred: {e}")
        poster_url = ""
        
    return poster_url

test = "inception"
search = sys.argv[1] if len(sys.argv) > 1 else test
print(get_movie_info(search))