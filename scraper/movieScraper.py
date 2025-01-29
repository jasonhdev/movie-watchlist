from flask import Flask, request, jsonify
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from dataclasses import dataclass, asdict
from datetime import datetime
import os
import re
import json
from dotenv import load_dotenv
import time
from typing import Optional
import atexit

load_dotenv(".env/.env")
chromedriver_path = os.getenv("CHROMEDRIVER_PATH")

# Flask app setup
app = Flask(__name__)

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
    "HBO Max",
    "Crunchyroll",
]

MEDIA_TYPES = [
    "movie", 
    "film", 
    "show", 
    "documentary", 
    "anime",
    "animated",
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
    "Rotten Tomatoes",
    "IMDb"
]

@dataclass
class Movie:
    title: Optional[str] = None
    description: Optional[str] = None
    tomato: Optional[str] = None
    imdb: Optional[str] = None
    image: Optional[str] = None
    trailer: Optional[str] = None
    rating: Optional[str] = None
    year: Optional[str] = None
    genre: Optional[str] = None
    runtime: Optional[str] = None
    services: Optional[str] = None
    releaseDate: Optional[str] = None
    
    def to_json(self):
        return json.dumps(asdict(self), indent=4)
    
def get_movie_info(search):
    try:
        movie = Movie()
        
        set_driver_search_url(search)
        
        movie.title = get_title()
        movie.services = get_services()
        movie.description = get_description() # Must be after get_services because of clicking issue
        movie.imdb, movie.tomato = get_scores()
        movie.releaseDate = get_release_date()
        movie.rating, movie.year, movie.genre, movie.runtime = get_meta_info()
        movie.trailer = get_trailer()
        movie.image = get_poster()
    except Exception as e:
        print(f"Error occurred: {e}")
        pass
        
    finally:
        return movie.to_json()
    
def set_driver_search_url(search):
    if search.startswith("http"):
        searchUrl = search
        driver.get(searchUrl)
            
    else:
        searchUrl = f"https://www.google.com/search?q={search}"
        driver.get(searchUrl)
        
        potential_movie = driver.find_elements(By.XPATH, " | ".join([f"//div[@role='complementary']//*[contains(text(), '{word}')]" for word in MOVIE_DETECT_KEYWORDS]))
        time.sleep(1)
        
        if not potential_movie:
            for media_type in MEDIA_TYPES:
                searchUrl = f"https://www.google.com/search?q={search} {media_type}"
                driver.get(searchUrl)
                time.sleep(1)
                potential_movie = driver.find_elements(By.XPATH, " | ".join([f"//div[@role='complementary']//*[contains(text(), '{word}')]" for word in MOVIE_DETECT_KEYWORDS]))
                if potential_movie:
                    break
                
        if not potential_movie:
            raise Exception ("Search term not identified as a movie")
    
def get_title():
    try:
        title = driver.find_element(By.XPATH, "//div[@data-attrid='title']").text

    except Exception as e:
        print(f"Error occurred: {e}")
        return None
        
    return title
    
def get_description():
    try:
        description_div = driver.find_element(By.XPATH, "//div[@data-attrid='description' or @data-attrid='VisualDigestDescription']")
        
        try:
            more_button = description_div.find_element(By.XPATH, ".//span[contains(@aria-label, 'More description')]")
            more_button.click()
            time.sleep(1)
        except Exception:
            pass

        description = description_div.text.replace("Description\n","")

    except Exception as e:
        print(f"Error occurred: {e}")
        return None
        
    return description
    
def get_scores():
    try:
        # IMDb Score
        imdb_score = driver.find_element(By.XPATH, "//span[text()='IMDb' and @aria-hidden='true']/preceding-sibling::span[contains(text(), '/10')]").text
        imdb_score = imdb_score.replace("IMDb", "").strip()
    except Exception as e:
        print(f"Error occurred: {e}")
        imdb_score = None
        
    try:
        # Rotten Tomatoes Score
        tomatoes_score = driver.find_element(By.XPATH, "//span[text()='Rotten Tomatoes' and @aria-hidden='true']/preceding-sibling::span[contains(text(), '%')]").text
        tomatoes_score = tomatoes_score.replace("Rotten Tomatoes", "").strip()

    except Exception as e:
        print(f"Error occurred: {e}")
        tomatoes_score = None
    
    return imdb_score, tomatoes_score
    
def get_trailer():
    try:
        trailer_url = driver.find_element(By.XPATH, "//a[contains(@href, 'youtube.com')]").get_attribute("href")
    except Exception as e:
        print(f"Error occurred: {e}")
        return None
    
    return trailer_url

def get_meta_info():
    # Google result ordering: Rating, Year, Genre, Runtime
    rating, year, genre, runtime = None, None, None, None
    possible_ratings = ['PG-13', 'PG', 'Not Rated', 'N/A', 'R', 'G']
    
    try:
        subtitle_element = driver.find_elements(By.XPATH, "//div[@data-attrid='subtitle']/span")
        meta_info = " ".join([span.text for span in subtitle_element]).split("‧")
        meta_info = [part.strip() for part in meta_info if part.strip() != ","]
    
        for info in meta_info:
            # Extract rating
            if not rating:
                rating = next((rating_value for rating_value in possible_ratings if rating_value in info), None)
                if rating:
                    info = info.replace(rating, '').strip() if rating else info
                    continue

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
            if not genre and genre not in ["Film", "Movie"]:
                genre = info
                
    except Exception as e:
        print(f"Error occurred: {e}")
        pass
    
    return rating, year, genre, runtime

def get_release_date():
    try:
        release_date = driver.find_element(By.XPATH, "//span[contains(text(), 'Release date')]/following-sibling::span").text
        release_date = re.sub(r"\((.*?)\)", "", release_date).replace("Release date:", "").replace("Initial release:", "").strip()
    
    except Exception as e:
        print(f"Error occurred: {e}")
        return None
        
    return release_date

def get_services():
    service_clean_name = {
        "Amazon Prime Video": "Amazon Prime",
        "Max": "HBO Max",
    }

    available_service = None
    
    try:
        where_watch_span = driver.find_element(By.XPATH, "//span[contains(text(), 'Where to watch')]")
        where_watch_span.click()
        time.sleep(1)
        
        where_watch_section = where_watch_span.find_element(By.XPATH, ".//ancestor::*[@jscontroller='qWD4e']/following-sibling::div")
        where_watch_text = where_watch_section.text.split("\n")
        
        for i in range(len(where_watch_text) - 1):
            service_name = where_watch_text[i]
            if where_watch_text[i + 1] == "Subscription":
                service_name = service_clean_name.get(service_name, service_name)

                if service_name in ACTIVE_SUBSCRIPTIONS:
                    available_service = service_name
                    break
                
    except Exception as e:
        print(f"Error occurred: {e}")
        pass
        
    return available_service

def get_poster():
    try:
        try:
            wiki_links = driver.find_elements(By.XPATH, "//a[contains(@href, 'wikipedia.org')]")
            current_url_before = driver.current_url
            for link in wiki_links:
                link.click()
                current_url_after = driver.current_url
                
                if current_url_before != current_url_after:
                    time.sleep(1)
                    break
                
        except Exception as e:
            pass

        poster_url = driver.find_element(By.XPATH, "//td[@class='infobox-image']//a[@class='mw-file-description']/img").get_attribute('src')

    except Exception as e:
        print(f"Error occurred: {e}")
        return None
        
    return poster_url

@app.route('/get-movie-info', methods=['GET'])
def movie_info():
    search = request.args.get('search')
    if not search:
        return jsonify({"error": "Missing 'search' query parameter"}), 400

    result = get_movie_info(search)
    return jsonify(json.loads(result))

@atexit.register
def shutdown_driver():
    if driver:
        print("Shutting down Selenium WebDriver.")
        driver.quit()
        
if __name__ == '__main__':
    app.run(debug=True, port=3001)