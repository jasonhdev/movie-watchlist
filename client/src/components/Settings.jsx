import { useState } from "react";
import Constants from "../Constants"

const Settings = ({ movie, currentTab, updateMovieCard }) => {

    const UPDATE_URL = process.env.REACT_APP_API_URL + '/movie/update/' + movie.id;
    const updateRequestOptions = {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
    }

    const toggleShowSettings = () => {
        setShowSettings(!showSettings);
    }

    const handleWatchAction = () => {

        movie.watched = currentTab === Constants.TAB_WATCH;

        const data = {
            'action': Constants.ACTION_WATCH,
            'watched': movie.watched,
            'released': true, // will always be released if movie is moved out of upcoming
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
        fetch(process.env.REACT_APP_API_URL + "/movie/delete/" + movie.id, updateRequestOptions);
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

        await fetch(UPDATE_URL, updateRequestOptions)
            .then((res) => res.json())
            .then((json) => {
                updateMovieCard(json);
            });
    }

    const handleAmcMoveAction = () => {
        fetch(process.env.REACT_APP_API_URL + '/amc/create/' + movie.id);

        movie.amc = 1;

        setShowSettings(false);
        updateMovieCard({
            'action': Constants.ACTION_AMC,
            'movie': movie,
        });
    }

    const [showSettings, setShowSettings] = useState(false);

    return (

        <>
            <div className="settingsContainer">
                <i onClick={toggleShowSettings} className="openSettingsBtn fas fa-ellipsis-v"></i>

                {showSettings &&
                    <>
                        <div className="settingsMenu">
                            <div className="actions">
                                <button onClick={currentTab === Constants.TAB_AMC ? handleAmcMoveAction : handleWatchAction}>
                                    <i className={movie.watched ? 'fas fa-video' : 'fas fa-eye'}></i>
                                    <span>{movie.watched || [Constants.TAB_UPCOMING, Constants.TAB_AMC].includes(currentTab) ?
                                        'Move to watch' : 'Watched'}
                                    </span>
                                </button>

                                {currentTab === 'watch' &&
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
            </div>
        </>
    );
}

export default Settings;