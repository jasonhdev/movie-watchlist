import './App.css';
import Movies from "./components/Movies"
import MovieCard from "./components/MovieCard"
import Header from "./components/Header/Header"

function App() {

  var data = require('./data.json');

  return (
    <div className="container">
      {/* <header className="App-header">
      </header> */}

      <Header></Header>

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
