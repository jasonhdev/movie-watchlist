import MovieCard from "./MovieCard"

const Movies = ({ movies }) => {
    return (
        <div className="movies">
            {movies !== null &&
                movies.map((movie, i) => {
                    return (
                        <MovieCard
                            key={i}
                            movie={movie}
                        />
                    );
                })
            }
        </div>
    );
};

export default Movies;