import { useState, useEffect, useRef } from "react";
import Constants from "../Constants"

const Settings = ({ movie, currentTab, handleMovieUpdate }) => {

    const UPDATE_URL = `http://localhost/WatchlistConversions/watchlistV2/api/public/api/movie/update/${movie.id}`;
    const updateRequestOptions = {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
        },
    }

    const toggleShowSettings = () => {
        setShowSettings(!showSettings);
    }

    const toggleWatched = async () => {
        updateRequestOptions.body = JSON.stringify({
            'action': Constants.ACTION_WATCH,
            'watched': !movie.watched
        })

        await fetch(UPDATE_URL, updateRequestOptions)
            .then((res) => res.json())
            .then((json) => handleMovieUpdate(json));
    }

    const toggleFeatured = async () => {
        updateRequestOptions.body = JSON.stringify({
            'action': Constants.ACTION_FEATURE,
            'featured': !movie.featured
        })

        await fetch(UPDATE_URL, updateRequestOptions)
            .then((res) => res.json())
            .then((json) => handleMovieUpdate(json));
    }

    const [showSettings, setShowSettings] = useState(false);

    return (

        <span>
            <i onClick={toggleShowSettings} className="fas fa-ellipsis-v openSettingsBtn"></i>

            {showSettings &&
                <>
                    <div className="settingsMenu">
                        <div className="actions">
                            <button onClick={toggleWatched}>
                                <i className={movie.watched ? 'fas fa-video' : 'fas fa-eye'}></i>
                                <span>{movie.watched || currentTab === Constants.TAB_UPCOMING ? 'Move to watch' : 'Watched'}</span>
                            </button>

                            {currentTab == 'watch' &&
                                <button onClick={toggleFeatured}>
                                    <i className="fas fa-star"></i>
                                    {movie.featured ? 'Unfeature' : 'Feature'}
                                </button>
                            }

                            {currentTab !== 'amc' &&
                                <>
                                    <button>
                                        <i className="fas fa-sync-alt"></i>
                                        <span>Refresh Info</span>
                                    </button>
                                    <button>
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