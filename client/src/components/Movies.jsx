import MovieCard from "./MovieCard"
import InfiniteScroll from 'react-infinite-scroll-component';
import { useState, useEffect } from "react";
import Constants from "../Constants";

const Movies = ({ movies, currentTab, updateMovieCard }) => {

    const DISPLAY_INCREMENT_COUNT = 10;

    const [displayCount, setDisplayCount] = useState(DISPLAY_INCREMENT_COUNT);
    const [hasMore, setHasMore] = useState(true);

    useEffect(() => {
        setHasMore(true);
        // Fix for infinite scroll loader not working within Modal, skip lazy load
        setDisplayCount(currentTab === Constants.TAB_AMC ? movies.length : DISPLAY_INCREMENT_COUNT);
    }, [movies, currentTab]);

    const loadMovies = () => {
        setDisplayCount(displayCount + DISPLAY_INCREMENT_COUNT);

        if (displayCount > movies.length) {
            setHasMore(false);
        }
    }

    return (
        <div className="movies">
            {movies.length > 0 &&
                <InfiniteScroll
                    dataLength={displayCount}
                    next={loadMovies}
                    hasMore={hasMore}
                    loader={<h4 key={0}>Loading...</h4>}
                >
                    {movies.slice(0, displayCount).map((movie, i) => {
                        return (
                            <MovieCard
                                key={i}
                                movie={movie}
                                currentTab={currentTab}
                                isLoading={movie.isLoading ?? false}
                                updateMovieCard={updateMovieCard}
                            />
                        );
                    })}
                </InfiniteScroll>
            }
        </div>
    );
};

export default Movies;