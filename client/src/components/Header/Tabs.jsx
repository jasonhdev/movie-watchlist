import "./Header.scss";

const Tabs = () => {

    let tabs = {
        'watch': 'Watch',
        'upcoming': 'Upcoming',
        'history': 'History',
    }

    return (
        <div className="tabs">
            {Object.entries(tabs).map(([tabKey, tabName]) => {
                return (
                    <button className={`tab-btn ${tabKey == 'watch' ? "active" : ""}`}>{tabName}</button>
                );
            })}
        </div>
    )
};

export default Tabs;