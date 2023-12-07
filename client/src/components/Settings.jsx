import { useState, useEffect, useRef } from "react";
import Constants from "../Constants"

const Settings = ({ movie, currentTab, updateMovieCard }) => {

    const UPDATE_URL = `http://localhost/WatchlistConversions/watchlistV2/api/public/api/movie/update/${movie.id}`;
    const updateRequestOptions = {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
    }

    const toggleShowSettings = () => {
        setShowSettings(!showSettings);
    }

    const handleWatchAction = () => {

        movie.watched = currentTab === Constants.TAB_WATCH;

        if (currentTab === Constants.TAB_UPCOMING) {
            movie.released = true;
        }

        const data = {
            'action': Constants.ACTION_WATCH,
            'watched': movie.watched,
            'released': movie.released,
        }

        updateRequestOptions.body = JSON.stringify(data)
        fetch(UPDATE_URL, updateRequestOptions);

        data.movie = movie;

        setShowSettings(false);
        updateMovieCard(data);
    }

    const handleFeatureAction = () => {
        movie.featured = !movie.featured;

        const data = {
            'action': Constants.ACTION_FEATURE,
            'featured': movie.featured,
        };

        updateRequestOptions.body = JSON.stringify(data);
        fetch(UPDATE_URL, updateRequestOptions);

        data.movie = movie;

        setShowSettings(false);
        updateMovieCard(data);
    }

    const handleDeleteAction = () => {
        setShowSettings(false);
        updateMovieCard({
            'action': 'delete',
            'movie': movie,
        });

        updateRequestOptions.method = "DELETE";
        fetch(`http://localhost/WatchlistConversions/watchlistV2/api/public/api/movie/delete/${movie.id}`, updateRequestOptions);
    }

    const handleRefreshAction = async () => {

        const data = {
            'action': Constants.ACTION_REFRESH,
            'searchTerm': movie.searchTerm,
        };

        updateRequestOptions.body = JSON.stringify(data);

        movie.isLoading = true;
        data.movie = movie;
        
        setShowSettings(false);
        updateMovieCard(data);

        await fetch(`http://localhost/WatchlistConversions/watchlistV2/api/public/api/movie/update/${movie.id}`, updateRequestOptions)
            .then((res) => res.json())
            .then((json) => {
                updateMovieCard(json);
            });
    }

    const [showSettings, setShowSettings] = useState(false);

    return (

        <span>
            <i onClick={toggleShowSettings} className="fas fa-ellipsis-v openSettingsBtn"></i>

            {showSettings &&
                <>
                    <div className="settingsMenu">
                        <div className="actions">
                            <button onClick={handleWatchAction}>
                                <i className={movie.watched ? 'fas fa-video' : 'fas fa-eye'}></i>
                                <span>{movie.watched || currentTab === Constants.TAB_UPCOMING ? 'Move to watch' : 'Watched'}</span>
                            </button>

                            {currentTab == 'watch' &&
                                <button onClick={handleFeatureAction}>
                                    <i className="fas fa-star"></i>
                                    {movie.featured ? 'Unfeature' : 'Feature'}
                                </button>
                            }

                            {currentTab !== 'amc' &&
                                <>
                                    <button onClick={handleRefreshAction}>
                                        <i className="fas fa-sync-alt"></i>
                                        <span>Refresh Info</span>
                                    </button>
                                    <button onClick={handleDeleteAction}>
                                        <i className="fas fa-trash"></i>
                                        <span>Delete</span>
                                    </button>
                                </>
                            }
                        </div>
                    </div>
                    <div id="overlay" onClick={toggleShowSettings}></div>
                </>
            }
        </span >
    );
}

export default Settings;