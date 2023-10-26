import './App.css';
import Movies from "./components/Movies"
import Header from "./components/Header/Header"

import { useState, useEffect } from "react";

function App() {
  const [currentTab, setCurrentTab] = useState('watch');
  const [movies, setMovies] = useState([]);
  const [moviesCache, setMoviesCache] = useState([]);

  useEffect(() => {
    const loadMovies = async () => {
      setMovies(await getMovies());
    }

    loadMovies();
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
    const response = await fetch('http://watchapi.pizzachicken.xyz/?list=' + list)
    return await response.json();
  }

  return (
    <div className="container">
      {/* <header className="App-header">
      </header> */}

      <Header handleTabChange={handleTabChange} handleSearchInput={handleSearchInput} currentTab={currentTab}></Header>
      <Movies movies={movies} currentTab={currentTab}></Movies>
    </div>
  );
}

export default App;
