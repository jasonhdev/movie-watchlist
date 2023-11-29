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
  const [isLoaded, setIsLoaded] = useState(false);

  // On page load
  useEffect(() => {
    const loadMovies = async () => {
      const watch = await fetchMovieList('watch');

      setMovies(watch);
      setMoviesCache({
        'watch': watch,
        'upcoming': await fetchMovieList('upcoming'),
        'history': await fetchMovieList('history'),
      });

      setIsLoaded(true);
    }

    loadMovies();

    // Set focus on search input anytime key is pressed
    document.addEventListener("keydown", () => { searchInputRef.current.focus() }, true);
  }, [])

  const handleTabChange = async (tab) => {
    setCurrentTab(tab);
    setMovies(moviesCache[tab]);
  }

  const handleSearchInput = (e) => {
    let search = e.target.value;

    setMovies(moviesCache[currentTab].filter((movie) => {
      return search.toLowerCase().split(' ').every(v => movie.title.toLowerCase().includes(v))
    }));
  }

  const refreshMovieUpdate = (data) => {
    const movieIndex = movies.findIndex(movie => movie.id === data.movie.id);
    const moviesRef = [...movies];

    moviesRef[movieIndex] = data.movie;

    setMovies(moviesRef);
  }

  const fetchMovieList = async (list = "watch") => {
    const response = await fetch(process.env.REACT_APP_API_URL + "?list=" + list);
    return await response.json();
  }

  return (
    isLoaded === true &&
    <div className="container">
      {/* <header className="App-header">
      </header> */}

      <Header handleTabChange={handleTabChange} handleSearchInput={handleSearchInput} currentTab={currentTab} searchInputRef={searchInputRef}></Header>
      <Movies movies={movies} currentTab={currentTab} handleMovieUpdate={handleMovieUpdate}></Movies>
    </div>
  );
}

export default App;
