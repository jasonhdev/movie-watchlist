import "./Header.scss";
import Constants from "../../Constants";

const Tabs = ({ handleTabChange, currentTab }) => {

    const tabs = {
        [Constants.TAB_WATCH]: 'Watch',
        [Constants.TAB_UPCOMING]: 'Upcoming',
        [Constants.TAB_HISTORY]: 'History',
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