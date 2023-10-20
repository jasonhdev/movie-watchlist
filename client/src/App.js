import './App.css';
import Movies from "./components/Movies"
import MovieCard from "./components/MovieCard"
import Header from "./components/Header/Header"
import { useState } from "react";

function App() {

  var data = require('./data.json');

  const [currentTab, setCurrentTab] = useState('watch');
  const [movies, setMovies] = useState();


  const handleTabChange = async (tab) => {
    setCurrentTab(tab);

    setMovies(await getMovies(tab));
  }

  const getMovies = async (list = "watch") => {
    // fetch('http://watchapi.pizzachicken.xyz/?list=' + list)
    //   .then((res) => res.json())
    //   .then((data) => {
    //     return data;
    //   });

    const response = await fetch('http://watchapi.pizzachicken.xyz/?list=' + list)
    return await response.json();
  }

  return (
    <div className="container">
      {/* <header className="App-header">
      </header> */}

      <Header handleTabChange={handleTabChange} currentTab={currentTab}></Header>

      <Movies>
        {movies.map((movie, i) => {
          return (
            <MovieCard
              key={i}
              movie={movie}
            />
          );
        })}
      </Movies>
    </div>
  );
}

export default App;
