import requests
from bs4 import BeautifulSoup
import re
import sys
import json
from datetime import datetime

HEADERS = {'User-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36'}
SUBSCRIPTIONS = ["Amazon", "Netflix", "Hulu", "Disneyplus"] #"Play"

movie = {
    "title": None,
    "description": None,
    "tomato": None,
    "imdb": None,
    "image": None,
    "trailer": None,
    "rating": None,
    "year": None,
    "genre": None,
    "runtime": None,
    "services": None,
    "releaseDate": None,
}

def get_image(search):
    # Fetch movie image from Wikipedia, find matching wiki page first
    search_url = search if search.startswith("http") else f"https://www.google.com/search?q={search} film wikipedia"
    response = requests.get(search_url, headers=HEADERS)
    content = BeautifulSoup(response.content, 'lxml')
    
    wiki_link = content.find("a", href=re.compile("https://en.wikipedia.org/"))['href']
    response = requests.get(wiki_link, headers=HEADERS)
    content = BeautifulSoup(response.content, 'lxml')
    
    imageElement = content.find("td", {"class": "infobox-image"})
    if imageElement:
        return imageElement.find("img")['src']

def get_movie_info(search):
    # Main content from Google search page result for scraping
    searchUrl = search if search.startswith("http") else f"https://www.google.com/search?q={search}"
    response = requests.get(searchUrl, headers=HEADERS)
    content = BeautifulSoup(response.content, 'lxml')
    
    # Check if movie is a film series
    if content.find(attrs={"data-attrid": "subtitle"}, string="Film series"):
        response = requests.get("https://www.google.com/search?q={search} 1 info", headers=HEADERS)
        content = BeautifulSoup(response.content, 'lxml')

    movie['title'] = get_title(content)
    movie['description'] = get_description(content)
    movie['imdb'], movie['tomato'] = get_reviews(content)
    movie["releaseDate"] = get_release_date(content)
    movie['rating'], movie['year'], movie['genre'] , movie['runtime'] = get_meta_info(content)
    movie['trailer'] = get_trailer(content)
    movie['services'] = get_services(content)
    movie['image'] = get_image(search)
    
    if movie["title"] != "See results about":
        return json.dumps(movie)

def get_title(content):
    titleElement = content.find(attrs={"data-attrid": "title"})
    return titleElement.text if titleElement else None 

def get_description(content):
    descriptionElement = content.find("div", attrs={'data-attrid': 'description'})
    if descriptionElement:
        return descriptionElement.findChildren("span")[0].text

def get_reviews(content):
    imdb, tomato = None, None
    
    reviewsElement = content.find('div', {'data-attrid': 'kc:/film/film:reviews'}) or content.find('div', {'data-attrid': 'kc:/tv/tv_program:reviews'})
    
    if reviewsElement:
        imdbElement = reviewsElement.find("span", attrs={"title": "IMDb"})
        if imdbElement:
            imdb = imdbElement.previous
            if "·" in imdb:
                imdb = imdbElement.previous.parent.previous
        
        tomatoElement = reviewsElement.find("span", attrs={"title": "Rotten Tomatoes"})
        if tomatoElement:
            tomato = tomatoElement.previous
            if "·" in tomato:
                tomato = tomatoElement.previous.parent.previous
        
    return imdb, tomato

def get_trailer(content):
    trailer_link = content.find("a", href=re.compile("https://www.youtube.com/"), attrs={'data-attrid': 'title_link'}) or content.find("a", href=re.compile("https://www.youtube.com/"))
    if trailer_link:
        return trailer_link['href']

def get_meta_info(content):
    # Google result ordering: Rating, Year, Genre, Runtime
    rating, year, genre, runtime = None, None, None, None
    
    meta_info_element = content.find(attrs={"data-attrid": "subtitle"})
    if meta_info_element:
        meta_info = meta_info_element.text.split("‧")
        meta_info = [part.strip() for part in meta_info if part.strip() != ","]
    
        for info in meta_info:
            if not rating:
                matching_text = next((val for val in ['PG-13', 'PG', 'Not Rated', 'N/A', 'R', 'G'] if val in info), None)
                if matching_text:
                    rating = matching_text
                    info = info.replace(matching_text, '').strip()

            if not year and len(info) == 4 and info.isnumeric():
                year = info
                continue
                
            # Searching for text matching "season(s)", "h", and "m"
            possible_runtime = re.search(r'(\d+\s*season[s]?)|((\d+)\s*h(?:\s*(\d+)\s*m)?)', info)
            if possible_runtime:
                matched_value = possible_runtime.group(0)
                runtime = matched_value
                continue
            
            if not genre:
                genre = info
    
    return rating, year, genre, runtime
    
def get_release_date(content):
    releaseDateElement = content.find(attrs={"data-attrid": "kc:/film/film:theatrical region aware release date"}) or content.find(attrs={"data-attrid": "kc:/film/film:release date"}) 
    if releaseDateElement:
        return re.sub(r"\((.*?)\)", "", releaseDateElement.text).replace("Release date:", "").replace("Initial release:", "").strip()

def get_services(content):
    serviceElement = content.find("div", attrs={"data-attrid": "kc:/film/film:media_actions_wholepage"}) or content.find("div", attrs={"data-attrid": "action:watch_film"}) or content.find("div", attrs={"data-attrid": "kc:/tv/tv_program:media_actions_wholepage"}) or None
    
    if serviceElement:
        service_url = serviceElement.find("a")
        if service_url:
            service_href = service_url['href']
            service_name = re.sub(r'https?://(www\.)?', '', service_href).split('.')[0].capitalize()
            if service_name in SUBSCRIPTIONS:
                
                if service_name == "Disneyplus":
                    service_name = "Disney+"
                    
                if service_name == "Amazon":
                    service_name = "Amazon Prime"
                
                if service_name == "Play":
                    service_name = "HBO Max"
                    
                return service_name
    return ""

def search_wrapper(search):
    media_types = ["movie", "film", "show", "documentary", "anime", str(datetime.now().year)]
    
    for media in media_types:
        try:
            movie_info = get_movie_info(f"{search} {media}")
            if movie_info:
                break
        except Exception as e:
            pass
        
    return movie_info if movie_info else None
    
search = sys.argv[1]
print(search_wrapper(search))