#xxxxxxxxxx!C:/Program Files/Python312/

import requests
from bs4 import BeautifulSoup
import re
import sys
import json

headers = ""
subscriptions = ["Amazon", "Netflix", "Hulu", "Disney", "Play"]
image = None

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
    "torrent": None,
    "releaseDate" : None,
    "watched": 0
}

#Image/poster
def getImage():
    global image, headers, search

    if search[0:4] == "http":
        searchUrl = search
    else:
        searchUrl = "https://www.google.com/search?q=" + search + " film wikipedia"
    
    result = requests.get(searchUrl, headers=headers)
    content = BeautifulSoup(result.content, 'lxml')

    wikiLink = content.find("a", href=re.compile("https://en.wikipedia.org/"))['href']

    result = requests.get(wikiLink, headers=headers)
    content = BeautifulSoup(result.content, 'lxml')

    image = content.find("td", {"class": "infobox-image"}).find("img")['src']

def getMovieInfo(search):

    title = None
    description = None
    tomato = None
    imdb = None
    trailer = None
    rating = None
    year = None
    genre = None
    runtime = None
    services = None
    releaseDate = None

    global headers

    # headers = {'User-agent': 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0'}
    # headers = {'User-agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36'}
    headers = {'User-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36'}

    
    if search[0:4] == "http":
        searchUrl = search
    else:
        searchUrl = "https://www.google.com/search?q=" + search + ""
    result = requests.get(searchUrl, headers=headers)
    content = BeautifulSoup(result.content, 'lxml')

#Account for movie series where explicit movie required
    if content.find(attrs={"data-attrid": "subtitle"}, string="Film series"):
        result = requests.get("https://www.google.com/search?q=" + search + " 1 info", headers=headers)
        content = BeautifulSoup(result.content, 'lxml')

    complimentaryDiv = content.findAll("div", attrs={"role": "complementary"})

    for aboutDiv in complimentaryDiv:
        if description is None or (imdb is None and tomato is None) or releaseDate is None:
            try:
                try:
                    description = aboutDiv.find("div", attrs={'data-attrid' : 'description'}).findChildren("span")[0].text
                except:
                    pass
                try:
                    imdb = aboutDiv.find("span", attrs={"title": "IMDb"}).previous
                except:
                    pass
                try:
                    tomato = aboutDiv.find("span", attrs={"title": "Rotten Tomatoes"}).previous

                    if "·" in tomato:
                        tomato = aboutDiv.find("span", attrs={"title": "Rotten Tomatoes"}).previous.parent.previous
                except:
                    pass
                try:
                    trailer = aboutDiv.find("a", href=re.compile("https://www.youtube.com/"))['href']
                except:
                    pass
                try:
                    releaseDate = content.find(attrs={"data-attrid": "kc:/film/film:theatrical region aware release date"})

                    if not releaseDate:
                        releaseDate = content.find(attrs={"data-attrid": "kc:/film/film:release date"})

                    releaseDate = releaseDate.text
                    releaseDate = re.sub(r"\((.*?)\)", "", releaseDate)
                    releaseDate = releaseDate.replace("Release date:", "")
                    releaseDate = releaseDate.replace("Initial release: ", "")
                    releaseDate = releaseDate.strip()
                except:
                    pass
            except:
                pass

# Direct search, maybe move into its own functions
    if not complimentaryDiv:
        if description is None or (imdb is None and tomato is None) or releaseDate is None:
            try:
                try:
                    reviewsDiv = content.find('div', {'data-attrid': 'kc:/film/film:reviews'})
                    
                    if not reviewsDiv:
                        reviewsDiv = content.find('div', {'data-attrid': 'kc:/tv/tv_program:reviews'})
                except:
                    pass
                try:
                    imdb = reviewsDiv.find("span", attrs={"title": "IMDb"}).previous
                except:
                    pass
                try:
                    tomato = reviewsDiv.find("span", attrs={"title": "Rotten Tomatoes"}).previous
                except:
                    pass
                try:
                    description = content.find("div", attrs={'data-attrid' : 'description'}).findChildren("span")[0].text
                except:
                    pass
                try:
                    trailer = content.find("a", href=re.compile("https://www.youtube.com/"), attrs={'data-attrid' : 'title_link'})
            
                    if not trailer:
                        trailer = content.find("a", href=re.compile("https://www.youtube.com/"))

                    trailer = trailer['href']

                except:
                    pass
                try:
                    releaseDate = content.find(attrs={"data-attrid": "kc:/film/film:theatrical region aware release date"})

                    if not releaseDate:
                        releaseDate = content.find(attrs={"data-attrid": "kc:/film/film:release date"})

                    releaseDate = releaseDate.text
                    releaseDate = releaseDate.replace(" (USA)", "")
                    releaseDate = releaseDate.replace("Release date: ", "")
                    releaseDate = releaseDate.replace("Initial release: ", "")
                except:
                    pass
            except:
                pass

    try:
        title = content.find(attrs={"data-attrid": "title"}).text
        info = content.find(attrs={"data-attrid": "subtitle"}).text
        info = info.split("‧")

        infoCleaned = []
        for x in range(0, len(info)):
            if x == 0:
                info1 = info[x].split()
                if info1[0] == 'PG' or info1[0] == 'PG-13' or info1[0] == 'R' or info1[0] == 'Not Rated' or info1[0] == 'N/A':
                    for info11 in info1:
                        infoCleaned.append(info11.strip())
                else:
                    infoCleaned.append(info[x].strip())
            else:
                trimmed = info[x].strip()
                
                if trimmed != ",":
                    infoCleaned.append(trimmed)

        info = infoCleaned

        x = 0
        if info[x] == 'PG' or info[x] == 'PG-13' or info[x] == 'R' or info[x] == 'Not Rated' or info[x] == 'N/A':
            rating = info[x]
            x = x + 1

        if len(info[x]) == 4 and info[x].isnumeric():
            year = info[x]
            x = x + 1

        genre = info[x]
        x = x + 1

        runtime = info[x]
        x = x + 1
    except:
        pass

    try:
        getImage()
    except:
        pass

#Streaming services
    serviceDiv = content.find("div", attrs={"data-attrid" : "kc:/film/film:media_actions_wholepage"})

    if not serviceDiv:
        serviceDiv = content.find("div", attrs={"data-attrid" : "action:watch_film"})

    if not serviceDiv:
        serviceDiv = content.find("div", attrs={"data-attrid" : "kc:/tv/tv_program:media_actions_wholepage"})

    if serviceDiv:
        subscription = serviceDiv.find("div", string="Subscription")
        premiumSubscription = serviceDiv.find("div", string="Premium subscription")
        free = serviceDiv.find("div", string="Free")

        services = list()
        
        if subscription or premiumSubscription or free:
            service = serviceDiv.find("a")['href']
            service = service.replace("https://", "")
            service = service.replace("http://", "")
            service = service.replace("www.", "")
            service = service[0:service.index(".")].capitalize()

            if subscription and service in subscriptions:
                if service == "Amazon":
                    service = "Amazon Prime"

                if service == "Play":
                    service = "HBO Max"
                
            if premiumSubscription:
                service = service + " (Premium)"

            services.append(service)

            if free:
                services.append(service)

        services = list(dict.fromkeys(services))
        services = ",".join(services)

    movie["title"] = title
    movie["description"] = description
    movie["tomato"] = tomato
    movie["imdb"] = imdb
    movie["image"] = image
    movie["trailer"] = trailer
    movie["rating"] = rating
    movie["year"] = year
    movie["genre"] = genre
    movie["runtime"] = runtime
    movie["releaseDate"] = releaseDate
    movie["services"] = services

    if year:
         yts = "https://yts.mx/movies/" + title.lower().replace(" ", "-") + "-" + year
         code = requests.get(yts, headers=headers).status_code 
         if code == 200:
            movie["torrent"] = yts

    if movie['title'] and movie['title'] != "See results about":
        return json.dumps(movie)

def searchWrapper(search, type = ""):
    try:
        if "stripMeta" in type:
            movieInfo = getMovieInfo(search + " ")
        else:
            movieInfo = getMovieInfo(search + " " +  type)

        if movieInfo == None:
            raise Exception("No data found")
        else:
            return json.dumps(movie) 
    except:
        if type == "":
            type = "movie"
        elif type == "movie":
            type = "film"
        elif type == "film":
            type = "show"
        elif type == "show":
            type = "documentary"
        elif type == "documentary":
            type = "stripMeta"
            search = search.split('-')[0]
            search = search.split('(')[0]
            search = search.strip() + " movie"
        elif type == "stripMeta":
            type = "stripMeta2"
            search = search.strip(" movie")
            search = search + " 2023"
        elif type == "stripMeta2":
            type = "not found"
        elif type == "not found":
            return
        
        return searchWrapper(search, type)

search = sys.argv[1]
print(searchWrapper(search))