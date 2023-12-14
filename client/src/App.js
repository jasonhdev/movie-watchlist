import './App.css';
import Movies from "./components/Movies"
import Header from "./components/Header/Header"
import Constants from "./Constants"
import 'react-responsive-modal/styles.css';
import { Modal } from 'react-responsive-modal';
import { useState, useEffect, useRef } from "react";

function App() {
  const [currentTab, setCurrentTab] = useState(Constants.TAB_WATCH);
  const [movies, setMovies] = useState([]);
  const [moviesCache, setMoviesCache] = useState([]);
  const [amcMovies, setAmcMovies] = useState([]);
  const [isLoaded, setIsLoaded] = useState(false);
  const [displayAmcModal, setDisplayAmcModal] = useState(false);
  const searchInputRef = useRef();

  // On page load
  useEffect(() => {
    const loadMovies = async () => {
      const watch = await fetchMovieList(Constants.TAB_WATCH);

      setMovies(watch);
      setMoviesCache({
        [Constants.TAB_WATCH]: watch,
        [Constants.TAB_UPCOMING]: await fetchMovieList(Constants.TAB_UPCOMING),
        [Constants.TAB_HISTORY]: await fetchMovieList(Constants.TAB_HISTORY),
      });

      const amcMovies = await fetchMovieList(Constants.TAB_AMC);
      setAmcMovies(amcMovies);

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

  const handleSearchInput = async (e) => {
    let search = e.target.value;

    if (e.code === "Enter" && search) {
      searchInputRef.current.value = "";

      moviesCache[currentTab].unshift({
        'title': search,
        'isLoading': true,
      });

      setMovies(moviesCache[currentTab]);

      await fetch(process.env.REACT_APP_API_URL + '/movie/create', {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          "list": currentTab,
          "searchTerm": search
        }),
      }).then((res) => res.json())
        .then((json) => {
          const moviesRef = [...moviesCache[currentTab]];
          const movie = json.movie;

          const index = moviesCache[currentTab].findIndex(movie => movie.title === search);
          moviesRef[index] = movie;

          moviesCache[currentTab] = moviesRef;

          setMovies(moviesRef);
        });

      return;
    }

    setMovies(moviesCache[currentTab].filter((movie) => {
      return search.toLowerCase().split(' ').every(v => movie.title.toLowerCase().includes(v))
    }));
  }

  const updateMovieCard = async (data) => {
    const index = movies.findIndex(movie => movie.id === data.movie.id);

    if (data.action === Constants.ACTION_WATCH) {
      const moviesRef = [...movies];

      moviesRef[index] = data.movie;

      if (!data.movie.watched) {
        moviesCache[Constants.TAB_WATCH].unshift(moviesRef[index]);
      } else {
        moviesCache[Constants.TAB_HISTORY].unshift(moviesRef[index]);
      }

      moviesRef.splice(index, 1);
      moviesCache[currentTab] = moviesRef;

      setMovies(moviesRef);

    } else if (data.action === Constants.ACTION_FEATURE) {
      const moviesRef = [...movies];
      moviesRef[index] = data.movie;

      setMovies(moviesRef);

    } else if (data.action === Constants.ACTION_DELETE) {
      const moviesRef = [...movies];

      moviesRef.splice(index, 1);
      moviesCache[currentTab] = moviesRef;

      setMovies(moviesRef);

    } else if (data.action === Constants.ACTION_REFRESH) {

      if (data.movie.isLoading) {
        const moviesRef = [...moviesCache[currentTab]];

        moviesRef[index] = data.movie;

        moviesCache[currentTab] = moviesRef;

        setMovies(moviesRef);

        return;
      }

      const moviesRef = [...movies];
      moviesRef[index] = data.movie

      moviesCache[currentTab] = moviesRef;

      setMovies(moviesRef);
    } else if (data.action === Constants.ACTION_AMC) {

      const amcIndex = amcMovies.findIndex(movie => movie.id === data.movie.id);

      const moviesRef = [...amcMovies];

      moviesRef.splice(amcIndex, 1);
      setAmcMovies(moviesRef);

      moviesCache[Constants.TAB_WATCH].unshift(data.movie);
    }
  }

  const fetchMovieList = async (list = Constants.TAB_WATCH) => {
    const response = await fetch(process.env.REACT_APP_API_URL + "?list=" + list);
    return await response.json();
  }

  const openAmcModal = () => {
    setDisplayAmcModal(true);
  }
  const closeAmcModal = () => {
    setDisplayAmcModal(false);
  }

  return (
    isLoaded === true &&
    <div className="container">
      <Header handleTabChange={handleTabChange} handleSearchInput={handleSearchInput} currentTab={currentTab} searchInputRef={searchInputRef}></Header>

      <div className="amcBtnContainer">
        <button className="amcBtn" onClick={openAmcModal}>Showings at AMC</button>
      </div>

      <Movies movies={movies} currentTab={currentTab} updateMovieCard={updateMovieCard}></Movies>

      <Modal open={displayAmcModal} onClose={closeAmcModal} center>
        <h3 className="amcLabel">Now Showing at AMC</h3>
        <Movies movies={amcMovies} currentTab={Constants.TAB_AMC} updateMovieCard={updateMovieCard} />
      </Modal>
    </div>
  );
}

export default App;
