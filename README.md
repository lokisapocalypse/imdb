# PHP movie repository

This project provides various repositories and methods for searching for movies or TV shows. For your convenience, I've provided a factory to create a repository. If you'd prefer a more hands on approach, you are free to build your own adapter class. You simply need to ensure your adapter implements the Fusani\Movies\Infrastructure\Provider\Adapter interface.

## Creating a repository

There are two ways to create a repository. The simplest way is to use the provided factory.

    use Fusani\Movies\Infrastructure\Persistence\OMDB;
    $factory = new OMDB\MovieRepositoryFactory();
    $repository = $factory->createRepository();

The other way to create a repository is to implement your own adapter and pass that in to the repository. This should be done if you prefer to use something other than Guzzle or you would like your adapter to do additional things either before or after the api call.

If you do this approach, you need to consider two things.

1. Your adapter class needs to implement Fusani\Movies\Infrastructure\Provider\Adapter
2. Your get method needs to return an array instead of json or xml

## Using the repository

Any method beginning with oneOf* will either return a Movie object or throw a NotFoundException. Methods beginning with manyWith* will return an array of Movie objects or an empty array in the event that no movies were found for the provided search. There are a few need to knows for each of the search methods.

### manyWithTitleLike ###

This method will return any movie matching the title provided. The api implementation will only return the first 10 results and does not support returning more rows. It does support pagination but that is not implemented at this time.

### oneOfId ###

Here, id represents an internal imdb id.

### oneOfTitle ###

This will return exactly one movie. In the event that more than one movie shares a title (Romeo and Juliet) for example, this will still only return one title. There is an optional year parameter that will allow you to be more specific about which movie you are looking for.
