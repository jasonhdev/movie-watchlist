import './Header.scss';
import Searchbar from './Searchbar.jsx';
import Tabs from './Tabs';

const Header = ({handleTabChange, handleSearchInput, currentTab}) => {

    return (
        <div className="header">
            <img src="pizzachicken.png" alt="Site logo"></img>
            <div className="searchBar">
                <Searchbar handleSearchInput={handleSearchInput} currentTab={currentTab}></Searchbar>
            </div>
            <Tabs handleTabChange={handleTabChange} currentTab={currentTab}></Tabs>
        </div>
    );
};

export default Header;