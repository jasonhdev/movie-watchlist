import { useState, useEffect, useRef } from "react";

const Settings = ({ movie, currentTab, handleMovieUpdate }) => {
    const toggleShowSettings = () => {
        setShowSettings(!showSettings);
    }

    const toggleFeatured = async () => {
        const postBody = { 'featured': !movie.featured };
        const requestOptions = {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(postBody)
        }

        const res = await fetch(`http://localhost/WatchlistConversions/watchlistV2/api/public/api/movie/update/${movie.id}`, requestOptions)
            .then((response) => {
                response.json().then((data) => {
                    console.log(data);
                    handleMovieUpdate(data);
                    return data;
                });
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
                            {currentTab == 'watch' &&
                                <>
                                    <button>
                                        <i className="fas fa-eye"></i>
                                        <span>Watched</span>
                                    </button>
                                    <button onClick={toggleFeatured}>
                                        <i className="fas fa-star"></i>
                                        {movie.featured ? 'Unfeature' : 'Feature'}
                                    </button>
                                </>
                            }

                            {(currentTab === 'upcoming' || currentTab === 'history' || currentTab === 'amc') &&
                                <button>
                                    <i className="fas fa-video"></i>
                                    <span>Move to Watch</span>
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