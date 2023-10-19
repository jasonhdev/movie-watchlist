import { useState } from "react";

import './Header.scss';
import Searchbar from './Searchbar.jsx';
import Tabs from './Tabs';

const Header = () => {

    const [currentTab, setCurrentTab] = useState('watch');

    const handleTabChange = (tab) => {
        setCurrentTab(tab);
    }

    return (
        <div className="header">
            <img src="pizzachicken.png" alt="Site logo"></img>
            <div className="searchBar">
                <Searchbar currentTab={currentTab}></Searchbar>
            </div>
            <Tabs handleTabChange={handleTabChange}></Tabs>
        </div>
    );
};

export default Header;