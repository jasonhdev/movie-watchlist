import './App.css';
import Movies from "./components/Movies"
import MovieCard from "./components/MovieCard"

function App() {

  var data = require('./data.json');

  return (
    <div className="App">
      {/* <header className="App-header">
      </header> */}

      <div className="movies">
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




    </div>
  );
}

export default App;
