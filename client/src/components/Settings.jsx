import { useState, useEffect, useRef } from "react";

const Settings = ({ movie, currentTab }) => {
    const toggleShowSettings = () => {
        setShowSettings(!showSettings);
    }

    const toggleFeatured = () => {
        // TODO: wire up buttons and add featured star
    }

    const [showSettings, setShowSettings] = useState(false);

    return (

        <span>
            <i onClick={toggleShowSettings} class="fas fa-ellipsis-v openSettingsBtn"></i>

            {showSettings &&
                <>
                    <div class="settingsMenu">
                        <div class="actions">
                            {currentTab == 'watch' &&
                                <>
                                    <button>
                                        <i class="fas fa-eye"></i>
                                        <span>Watched</span>
                                    </button>
                                    <button onClick={toggleFeatured}>
                                        <i class="fas fa-star"></i>
                                        {movie.featured ? 'Unfeature' : 'Feature'}
                                    </button>
                                </>
                            }

                            {(currentTab === 'upcoming' || currentTab === 'history' || currentTab === 'amc') &&
                                <button>
                                    <i class="fas fa-video"></i>
                                    <span>Move to Watch</span>
                                </button>
                            }

                            {currentTab !== 'amc' &&
                                <>
                                    <button>
                                        <i class="fas fa-sync-alt"></i>
                                        <span>Refresh Info</span>
                                    </button>
                                    <button>
                                        <i class="fas fa-trash"></i>
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