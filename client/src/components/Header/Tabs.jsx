import "./Header.scss";

const Tabs = ({ handleTabChange, currentTab }) => {

    let tabs = {
        'watch': 'Watch',
        'upcoming': 'Upcoming',
        'history': 'History',
    };

    return (
        <div className="tabs">
            {Object.entries(tabs).map(([tabKey, tabName]) => {
                return (
                    <button
                        key={tabKey}
                        className={`tab-btn ${tabKey === currentTab ? 'active' : ''}`}
                        onClick={() => {
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