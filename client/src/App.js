import './App.css';
import Movies from "./components/Movies"
import Header from "./components/Header/Header"
import Constants from "./Constants"

import { useState, useEffect, useRef } from "react";

function App() {
  const [currentTab, setCurrentTab] = useState(Constants.TAB_WATCH);
  const [movies, setMovies] = useState([]);
  const [moviesCache, setMoviesCache] = useState([]);
  const searchInputRef = useRef();

  // On page load
  useEffect(() => {
    const loadMovies = async () => {
      setMovies(await getMovies());
    }

    loadMovies();

    // Set focus on search input anytime key is pressed
    document.addEventListener("keydown", () => { searchInputRef.current.focus() }, true);
  }, [])

  const handleTabChange = async (tab) => {
    setCurrentTab(tab);
    setMovies(await getMovies(tab));
  }

  const handleSearchInput = (e) => {
    let search = e.target.value;

    if (!search) {
      setMovies(moviesCache);
      setMoviesCache([]);
    } else {
      if (!moviesCache.length) {
        setMoviesCache(movies);
        setMovies(movies.filter((movie) => {
          return search.toLowerCase().split(' ').every(v => movie.title.toLowerCase().includes(v))
        }));
      }
      else {
        setMovies(moviesCache.filter((movie) => {
          return search.toLowerCase().split(' ').every(v => movie.title.toLowerCase().includes(v))
        }));
      }
    }
  }

  const getMovies = async (list = "watch") => {
    const response = await fetch(process.env.REACT_APP_API_URL + "?list=" + list);
    return await response.json();
  }

  return (
    <div className="container">
      {/* <header className="App-header">
      </header> */}

      <Header handleTabChange={handleTabChange} handleSearchInput={handleSearchInput} currentTab={currentTab} searchInputRef={searchInputRef}></Header>
      <Movies movies={movies} currentTab={currentTab}></Movies>
    </div>
  );
}

export default App;
