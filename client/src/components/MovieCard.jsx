import './MovieCard.scss';

const MovieCard = ({ movie, currentTab }) => {
    return (
        <div className="movieCard">
            <div className="posterContainer">
                <a href={movie.trailer} target="_blank" rel="noreferrer">
                    <img className={movie.image ? "fallbackPoster" : ""} src={movie.image ? 'https://' + movie.image : "default.png"} alt={'Movie poster for ' + movie.search}></img>
                </a>
            </div>

            <div className="infoContainer">
                <div className="titleRow">
                    <a target="_blank" rel="noreferrer" href={'https://www.google.com/search?q=' + movie.title}>{movie.title}</a>
                    {/* TODO: Add settings */}
                </div>

                <p className="metaDataRow">
                    {movie.rating && <span className="rating">{movie.rating}</span>}
                    {movie.year && <span>{movie.year}</span>}
                    {movie.runtime && <span>{movie.runtime}</span>}
                </p>

                <p>
                    <i>{movie.genre}</i>
                </p>

                {
                    (currentTab == 'watch' && movie.services) ? <p>Watch on: {movie.services}</p>
                        : (currentTab == 'upcoming') ? <p>Release Date: {movie.release_date ?? "TBD"}</p>
                            : (currentTab == 'history') ? <p>Watched on: {movie.watched_date}</p>
                                : ""
                }

                {(movie.tomato || movie.imdb) &&
                    <div className="scoresRow">
                        {movie.tomato &&
                            <span>
                                <img className="tomatoLogo" src="tomato.png" alt="Logo for Rotten Tomatos"></img>
                                <span className="score">{movie.tomato}</span>
                            </span>
                        }

                        {movie.imdb &&
                            <span>
                                <img className="imdbLogo" src="imdb.png" alt="Logo for IMDB"></img>
                                <span className="score">{movie.imdb}</span>
                            </span>
                        }
                    </div>
                }

                {/* TODO: Expand/hide desc */}
                <p className="description">{movie.description}</p>
            </div>
        </div>
    );
};

export default MovieCard;