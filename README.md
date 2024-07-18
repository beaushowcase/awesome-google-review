# Scrapping Google Reviews by #beaubhavik

## Usage

To get started with scrapping Google Reviews using the Awesome Google Review plugin, follow the steps below.

### Free API Key

Use your free API key:

```shell
beau62e081f846bbb5f452e426de67d7
```

### Display Reviews

Ensure that the Awesome Google Review plugin is active before using this code.

Below is an example of how to use the function to get 5-star reviews by term ID.

```php
$term_ids = array(31,32);  // Actual Term IDs of the business taxonomy

// Check if the function get_all_reviews_by_term exists
if (function_exists('get_all_reviews_by_term')) {
    // Retrieve all 5-star reviews (true = 5 Star Reviews, false = All Reviews)
    $all_reviews = get_all_reviews_by_term($term_ids, true);
} else {
    echo "Please activate the Awesome Google Review plugin.";
}
```

Make sure to replace 13 with the actual term ID relevant to your business taxonomy.

## Admin Panel

After scrapping reviews, access them through:
```shell
/wp-admin/admin.php?page=awesome-google-review
```


