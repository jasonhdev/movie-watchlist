import MovieCard from "./MovieCard"
import InfiniteScroll from 'react-infinite-scroller';
import { useState, useEffect } from "react";

const Movies = ({ movies, currentTab }) => {

    const displayIncrementCount = 10;

    const [displayCount, setDisplayCount] = useState(displayIncrementCount);
    const [hasMore, setHasMore] = useState(true);

    useEffect(() => {
        setHasMore(true);
        setDisplayCount(displayIncrementCount);
    }, [movies]);

    const loadMovies = () => {
        setDisplayCount(displayCount + displayIncrementCount);

        if (displayCount > movies.length) {
            setHasMore(false);
        }
    }

    return (
        <div className="movies">
            {movies !== null &&
                <InfiniteScroll
                    loadMore={loadMovies}
                    hasMore={hasMore}
                    loader={<h4>Loading...</h4>}
                >
                    {movies.slice(0, displayCount).map((movie, i) => {
                        return (
                            <MovieCard
                                key={i}
                                movie={movie}
                                currentTab={currentTab}
                            />
                        );
                    })}
                </InfiniteScroll>
            }
        </div>
    );
};

export default Movies;