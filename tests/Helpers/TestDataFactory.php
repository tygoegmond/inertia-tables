<?php

namespace Egmond\InertiaTables\Tests\Helpers;

use Egmond\InertiaTables\Tests\Database\Models\User;
use Egmond\InertiaTables\Tests\Database\Models\Post;
use Egmond\InertiaTables\Tests\Database\Models\Category;
use Egmond\InertiaTables\Tests\Database\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

class TestDataFactory
{
    /**
     * Create basic user data for testing
     */
    public static function createUsers(int $count = 5, array $overrides = []): Collection
    {
        return User::factory()->count($count)->create($overrides);
    }
    
    /**
     * Create users with specific states
     */
    public static function createUsersWithStates(array $states = ['active', 'inactive'], int $countPerState = 2): array
    {
        $users = [];
        
        foreach ($states as $state) {
            $users[$state] = User::factory()->count($countPerState)->create(['status' => $state]);
        }
        
        return $users;
    }
    
    /**
     * Create categories for testing
     */
    public static function createCategories(int $count = 3, array $overrides = []): Collection
    {
        return Category::factory()->count($count)->create($overrides);
    }
    
    /**
     * Create posts with relationships
     */
    public static function createPosts(int $count = 10, ?Collection $users = null, ?Collection $categories = null, array $overrides = []): Collection
    {
        $users = $users ?? self::createUsers(3);
        $categories = $categories ?? self::createCategories(2);
        
        return Post::factory()->count($count)->create(array_merge([
            'user_id' => fn() => $users->random()->id,
            'category_id' => fn() => $categories->random()->id,
        ], $overrides));
    }
    
    /**
     * Create posts with specific statuses
     */
    public static function createPostsWithStatuses(array $statuses = ['draft', 'published', 'archived'], int $countPerStatus = 3): array
    {
        $users = self::createUsers(5);
        $categories = self::createCategories(3);
        $posts = [];
        
        foreach ($statuses as $status) {
            $posts[$status] = Post::factory()->count($countPerStatus)->create([
                'status' => $status,
                'user_id' => fn() => $users->random()->id,
                'category_id' => fn() => $categories->random()->id,
            ]);
        }
        
        return $posts;
    }
    
    /**
     * Create comments with relationships
     */
    public static function createComments(int $count = 15, ?Collection $users = null, ?Collection $posts = null): Collection
    {
        $users = $users ?? self::createUsers(5);
        $posts = $posts ?? self::createPosts(8, $users);
        
        return Comment::factory()->count($count)->create([
            'user_id' => fn() => $users->random()->id,
            'post_id' => fn() => $posts->random()->id,
        ]);
    }
    
    /**
     * Create a complete test dataset with all relationships
     */
    public static function createCompleteDataset(array $config = []): array
    {
        $config = array_merge([
            'users' => 10,
            'categories' => 5,
            'posts' => 25,
            'comments' => 50,
        ], $config);
        
        $users = self::createUsers($config['users']);
        $categories = self::createCategories($config['categories']);
        $posts = self::createPosts($config['posts'], $users, $categories);
        $comments = self::createComments($config['comments'], $users, $posts);
        
        return [
            'users' => $users,
            'categories' => $categories,
            'posts' => $posts,
            'comments' => $comments,
        ];
    }
    
    /**
     * Create test data for search functionality
     */
    public static function createSearchableData(): array
    {
        $users = collect([
            User::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']),
            User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']),
            User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@company.com']),
        ]);
        
        $categories = collect([
            Category::factory()->create(['name' => 'Technology']),
            Category::factory()->create(['name' => 'Business']),
            Category::factory()->create(['name' => 'Science']),
        ]);
        
        $posts = collect([
            Post::factory()->create([
                'title' => 'Laravel Framework Guide',
                'user_id' => $users->first()->id,
                'category_id' => $categories->first()->id,
            ]),
            Post::factory()->create([
                'title' => 'Business Strategy Planning',
                'user_id' => $users->get(1)->id,
                'category_id' => $categories->get(1)->id,
            ]),
            Post::factory()->create([
                'title' => 'Scientific Research Methods',
                'user_id' => $users->last()->id,
                'category_id' => $categories->last()->id,
            ]),
        ]);
        
        return [
            'users' => $users,
            'categories' => $categories,
            'posts' => $posts,
        ];
    }
    
    /**
     * Create test data for sorting functionality
     */
    public static function createSortableData(): array
    {
        $baseDate = now()->subDays(10);
        
        $users = collect([
            User::factory()->create(['name' => 'Alice', 'created_at' => $baseDate->copy()->addDays(1)]),
            User::factory()->create(['name' => 'Bob', 'created_at' => $baseDate->copy()->addDays(2)]),
            User::factory()->create(['name' => 'Charlie', 'created_at' => $baseDate->copy()->addDays(3)]),
        ]);
        
        $posts = collect([
            Post::factory()->create([
                'title' => 'First Post',
                'created_at' => $baseDate->copy()->addDays(1),
                'user_id' => $users->get(0)->id,
            ]),
            Post::factory()->create([
                'title' => 'Second Post',
                'created_at' => $baseDate->copy()->addDays(2),
                'user_id' => $users->get(1)->id,
            ]),
            Post::factory()->create([
                'title' => 'Third Post',
                'created_at' => $baseDate->copy()->addDays(3),
                'user_id' => $users->get(2)->id,
            ]),
        ]);
        
        return [
            'users' => $users,
            'posts' => $posts,
        ];
    }
    
    /**
     * Create test data for pagination functionality
     */
    public static function createPaginationData(int $totalRecords = 50): array
    {
        $users = self::createUsers($totalRecords);
        $categories = self::createCategories(5);
        $posts = self::createPosts($totalRecords, $users, $categories);
        
        return [
            'users' => $users,
            'categories' => $categories,
            'posts' => $posts,
        ];
    }
    
    /**
     * Create test data for action functionality
     */
    public static function createActionData(): array
    {
        $users = self::createUsers(5);
        $categories = self::createCategories(3);
        
        $posts = collect([
            // Draft posts (can be published)
            ...Post::factory()->count(3)->create([
                'status' => 'draft',
                'user_id' => fn() => $users->random()->id,
                'category_id' => fn() => $categories->random()->id,
            ]),
            // Published posts (can be archived)
            ...Post::factory()->count(3)->create([
                'status' => 'published',
                'user_id' => fn() => $users->random()->id,
                'category_id' => fn() => $categories->random()->id,
            ]),
            // Archived posts (limited actions)
            ...Post::factory()->count(2)->create([
                'status' => 'archived',
                'user_id' => fn() => $users->random()->id,
                'category_id' => fn() => $categories->random()->id,
            ]),
        ]);
        
        return [
            'users' => $users,
            'categories' => $categories,
            'posts' => $posts,
        ];
    }
    
    /**
     * Create test data for relationship functionality
     */
    public static function createRelationshipData(): array
    {
        $users = self::createUsers(3);
        $categories = self::createCategories(2);
        $posts = self::createPosts(6, $users, $categories);
        $comments = self::createComments(12, $users, $posts);
        
        return [
            'users' => $users,
            'categories' => $categories,
            'posts' => $posts,
            'comments' => $comments,
        ];
    }
    
    /**
     * Create minimal test data for performance testing
     */
    public static function createMinimalData(): array
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        
        return [
            'user' => $user,
            'category' => $category,
            'post' => $post,
        ];
    }
    
    /**
     * Create large dataset for performance testing
     */
    public static function createLargeDataset(int $multiplier = 10): array
    {
        $users = self::createUsers(10 * $multiplier);
        $categories = self::createCategories(5 * $multiplier);
        $posts = self::createPosts(50 * $multiplier, $users, $categories);
        $comments = self::createComments(100 * $multiplier, $users, $posts);
        
        return [
            'users' => $users,
            'categories' => $categories,
            'posts' => $posts,
            'comments' => $comments,
        ];
    }
    
    /**
     * Create test data with specific attribute patterns
     */
    public static function createPatternedData(string $pattern = 'alphabetical'): array
    {
        switch ($pattern) {
            case 'alphabetical':
                $users = collect([
                    User::factory()->create(['name' => 'Alice Johnson']),
                    User::factory()->create(['name' => 'Bob Smith']),
                    User::factory()->create(['name' => 'Charlie Brown']),
                    User::factory()->create(['name' => 'Diana Prince']),
                ]);
                break;
                
            case 'numerical':
                $users = collect(range(1, 5))->map(fn($i) => 
                    User::factory()->create(['name' => "User {$i}"])
                );
                break;
                
            case 'mixed':
                $users = collect([
                    User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']),
                    User::factory()->create(['name' => 'Bob', 'email' => 'bob@company.org']),
                    User::factory()->create(['name' => 'Charlie', 'email' => 'charlie@test.net']),
                ]);
                break;
                
            default:
                $users = self::createUsers(4);
        }
        
        return ['users' => $users];
    }
    
    /**
     * Clean up all test data
     */
    public static function cleanup(): void
    {
        Comment::query()->delete();
        Post::query()->delete();
        Category::query()->delete();
        User::query()->delete();
    }
    
    /**
     * Get random record from collection
     */
    public static function getRandomRecord(Collection $collection)
    {
        return $collection->random();
    }
    
    /**
     * Get first N records from collection
     */
    public static function getFirstRecords(Collection $collection, int $count): Collection
    {
        return $collection->take($count);
    }
    
    /**
     * Filter collection by attribute
     */
    public static function filterByAttribute(Collection $collection, string $attribute, $value): Collection
    {
        return $collection->where($attribute, $value);
    }
}