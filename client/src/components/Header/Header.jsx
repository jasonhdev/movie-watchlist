import './Header.scss';
import Searchbar from './Searchbar.jsx';
import Tabs from './Tabs';

const Header = () => {
    return (
        <div className="header">
            <img src="pizzachicken.png"></img>
            <div className="searchBar">
                <Searchbar></Searchbar>
            </div>
            <Tabs></Tabs>
        </div>
    );
};

export default Header;