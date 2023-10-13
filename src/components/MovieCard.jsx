import './MovieCard.scss';

const MovieCard = ({ movie }) => {
    return (
        <div className="movieCard">
            <div className="posterContainer">
                <img src={'https://' + movie.image} alt={'Movie poster for ' + movie.search}></img>
            </div>

            <div className="infoContainer">
                <div className="titleRow">
                    <a target="_blank" rel="noreferrer" href={'https://www.google.com/search?q=' + movie.title}>{movie.title}</a>
                    {/* TODO: Add settings */}
                </div>

                <p className="metaDataRow">
                    {movie.rating &&
                        <span className="rating">{movie.rating}</span>
                    }
                    <span>{movie.year}</span>
                    <span>{movie.runtime}</span>
                </p>

                <p>
                    <i>{movie.genre}</i>
                </p>

                {(movie.tomato || movie.imdb) &&
                    <div className="scoresRow">
                        {movie.tomato &&
                            <span>
                                <img class="tomatoLogo" src="tomato.png"></img>
                                <span class="score">{movie.tomato}</span>
                            </span>
                        }

                        {movie.imdb &&
                            <span>
                                <img class="imdbLogo" src="imdb.png"></img>
                                <span class="score">{movie.imdb}</span>
                            </span>
                        }
                    </div>
                }

                {/* TODO: Mising logic */}
                <p>Watch on:</p>

                {/* TODO: Expand/hide desc */}
                <p>{movie.description}</p>
            </div>
        </div>
    );
};

export default MovieCard;