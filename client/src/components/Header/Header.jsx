import './Header.scss';
import Searchbar from './Searchbar.jsx';
import Tabs from './Tabs';

const Header = ({handleTabChange, currentTab}) => {

    return (
        <div className="header">
            <img src="pizzachicken.png" alt="Site logo"></img>
            <div className="searchBar">
                <Searchbar currentTab={currentTab}></Searchbar>
            </div>
            <Tabs handleTabChange={handleTabChange} currentTab={currentTab}></Tabs>
        </div>
    );
};

export default Header;