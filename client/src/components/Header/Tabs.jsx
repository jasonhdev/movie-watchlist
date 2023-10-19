import "./Header.scss";
import { useState } from "react";

const Tabs = ({ handleTabChange }) => {

    let tabs = {
        'watch': 'Watch',
        'upcoming': 'Upcoming',
        'history': 'History',
    };

    const [currentTab, setCurrentTab] = useState('watch');

    return (
        <div className="tabs">
            {Object.entries(tabs).map(([tabKey, tabName]) => {
                return (
                    <button
                        key={tabKey}
                        className={`tab-btn ${tabKey === currentTab ? 'active' : ''}`}
                        onClick={() => {
                            setCurrentTab(tabKey);
                            handleTabChange(tabKey);
                        }}>
                        {tabName}
                    </button>
                );
            })}
        </div>
    )
};

export default Tabs;