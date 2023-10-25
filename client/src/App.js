import './App.css';
import Movies from "./components/Movies"
import Header from "./components/Header/Header"

import { useState, useEffect } from "react";

function App() {
  const [currentTab, setCurrentTab] = useState('watch');
  const [movies, setMovies] = useState([]);

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

  const getMovies = async (list = "watch") => {
    const response = await fetch('http://watchapi.pizzachicken.xyz/?list=' + list)
    return await response.json();
  }

  return (
    <div className="container">
      {/* <header className="App-header">
      </header> */}

      <Header handleTabChange={handleTabChange} currentTab={currentTab}></Header>
      <Movies movies={movies} currentTab={currentTab}></Movies>
    </div>
  );
}

export default App;
