import './MovieCard.scss';
import './Settings';
import Settings from './Settings';
import Constants from "../Constants";
import { useState, useEffect } from "react";

const MovieCard = ({ movie, currentTab, isLoading, updateMovieCard }) => {

    const [displayExtraView, setDisplayExtraView] = useState(false);

    const toggleExtraView = () => {
        setDisplayExtraView(!displayExtraView);
    }

    const getInfoSection = () => {
        if (isLoading) {
            return <div className="loadingRing"><span></span></div>
        }

        return <div className="subInfoContainer">
            <p className="metaDataRow">
                <span className="rating">{movie.rating ?? "NA"}</span>
                {(currentTab !== Constants.TAB_UPCOMING && movie.year) && <span>{movie.year}</span>}
                {movie.runtime && <span>{movie.runtime}</span>}
            </p>

            <p>
                <i>{movie.genre}</i>
            </p>

            {
                (currentTab === Constants.TAB_WATCH && movie.amc) ? <img src="amc.png" className="amcLogo"></img> :
                    (currentTab === Constants.TAB_WATCH && movie.services) ? <p>Watch on: {movie.services}</p>
                        : (currentTab === Constants.TAB_UPCOMING) ? <p>Release Date: {movie.release_date ?? "TBD"}</p>
                            : (currentTab === Constants.TAB_HISTORY) ? <p>Watched on: {movie.watched_date}</p>
                                : ""
            }

            <div class='expandableSection'>
                {
                    (movie.tomato || movie.imdb) &&
                    <div className="scoresRow">
                        <span className="tomatoCol">
                            {movie.tomato &&
                                <>
                                    <img src="tomato.png" alt="Logo for Rotten Tomato"></img>
                                    <span className="score">{movie.tomato}</span>
                                </>
                            }
                        </span>
                        {movie.imdb &&
                            <span className="imdbCol">
                                <img className="imdbLogo" src="imdb.png" alt="Logo for IMDB"></img>
                                <span className="score">{movie.imdb}</span>
                            </span>
                        }
                    </div>
                }

                <hr></hr>
                <p className="description" onClick={toggleExtraView}>{movie.description}</p>
            </div>
        </div>
    }

    return (
        <div movieid={movie.id} className={"movieCard " + (displayExtraView ? 'extraView' : 'mainView')}>
            <div className="posterContainer">
                {(movie.featured > 0 && currentTab === Constants.TAB_WATCH) && <i className="fas fa-star featuredStar"></i>}
                <a href={movie.trailer_url} target="_blank" rel="noreferrer">
                    <span className={movie.poster_url ? "" : "fallbackPoster"}>
                        <img src={movie.poster_url ? 'https://' + movie.poster_url : "default.png"} alt={'Movie poster for ' + movie.title}></img>
                    </span>
                </a>
            </div>

            <div className="infoContainer">
                <div className="titleRow">
                    <a className="movieTitle" target="_blank" rel="noreferrer" href={'https://www.google.com/search?q=' + movie.title}>{movie.title}</a>
                    <Settings movie={movie} currentTab={currentTab} updateMovieCard={updateMovieCard}></Settings>
                </div>

                {getInfoSection()}

            </div>
        </div>
    );
};

export default MovieCard;