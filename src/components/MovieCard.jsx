import './MovieCard.css';

const MovieCard = ({ movie }) => {
    return (
        <div>
            <img class="movieImg" src="default.png"></img>

            <h2>{movie.title}</h2>

            <p><span><span>{movie.rating}</span>&nbsp;‧&nbsp;</span>
                <span>{movie.year} &nbsp;‧&nbsp; </span>
                <span>{movie.runtime}</span>
            </p>
            <p><i>{movie.genre}</i></p>
            <p>Watch on {movie.services}</p>

            <p>Tomato</p>
            <p>IMdB</p>

            <p>{movie.description}</p>

        </div>
    );
};

export default MovieCard;