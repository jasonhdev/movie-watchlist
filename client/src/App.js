import './App.css';
import Movies from "./components/Movies"
import MovieCard from "./components/MovieCard"
import Header from "./components/Header/Header"
import { useState } from "react";

function App() {

  var data = require('./data.json');

  const [currentTab, setCurrentTab] = useState('watch');

  const handleTabChange = (tab) => {
    setCurrentTab(tab);
  }

  return (
    <div className="container">
      {/* <header className="App-header">
      </header> */}

      <Header handleTabChange={handleTabChange} currentTab={currentTab}></Header>

      <Movies>
        {data.map((movie, i) => {
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
