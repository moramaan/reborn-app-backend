<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;

class ItemsTableSeeder extends Seeder
{
    /**
     * Spanish states and cities mapping.
     */
    private $spanishLocations = [
        'Andalucía' => ['Sevilla', 'Málaga', 'Cádiz', 'Córdoba', 'Granada', 'Huelva', 'Jaén', 'Almería'],
        'Aragón' => ['Zaragoza', 'Huesca', 'Teruel'],
        'Asturias' => ['Oviedo', 'Gijón', 'Avilés'],
        'Islas Baleares' => ['Palma de Mallorca', 'Ibiza', 'Manacor'],
        'Canarias' => ['Las Palmas de Gran Canaria', 'Santa Cruz de Tenerife'],
        'Cantabria' => ['Santander', 'Torrelavega'],
        'Castilla-La Mancha' => ['Toledo', 'Albacete', 'Ciudad Real', 'Guadalajara', 'Cuenca'],
        'Castilla y León' => ['Valladolid', 'León', 'Salamanca', 'Burgos', 'Palencia', 'Ávila', 'Segovia', 'Soria', 'Zamora'],
        'Cataluña' => ['Barcelona', 'Lérida', 'Gerona', 'Tarragona'],
        'Comunidad Valenciana' => ['Valencia', 'Alicante', 'Castellón de la Plana'],
        'Extremadura' => ['Badajoz', 'Cáceres'],
        'Galicia' => ['La Coruña', 'Lugo', 'Orense', 'Pontevedra'],
        'Madrid' => ['Madrid'],
        'Murcia' => ['Murcia'],
        'Navarra' => ['Pamplona'],
        'País Vasco' => ['Bilbao', 'Vitoria', 'San Sebastián'],
        'La Rioja' => ['Logroño'],
    ];

    public function run()
    {
        // Create a user using the UserFactory with a random Spanish location
        $user = User::factory()->create([
            'city' => $this->getRandomCity(),
            'state' => $this->getRandomState(),
        ]);

        // Path to the JSON file
        $jsonPath = database_path("seeders/demoData/seedData.json");

        // Read the JSON file
        $jsonData = file_get_contents($jsonPath);

        // Decode JSON data to an array
        $items = json_decode($jsonData, true);

        foreach ($items as $itemData) {
            // Prepare the data for insertion
            $item = new Item([
                'id' => $itemData['id'],
                'userId' => $user->id,
                'title' => $itemData['title'],
                'description' => $itemData['description'],
                'price' => floatval(str_replace(['.', '€', ','], ['', '', '.'], $itemData['price'])),
                'category' => $itemData['category'],
                'location' => "{$user->city}, {$user->state}", // Concatenate user's city and state
                'state' => $itemData['reserved'] ? 'reserved' : 'available',
                'condition' => $this->mapCondition($itemData['condition']),
                'publishDate' => now()->toDateString(), // Or any specific date
                'images' => json_encode($itemData['images']),
            ]);

            // Save the item
            $item->save();
        }
    }

    /**
     * Map the condition to the database value.
     */
    private function mapCondition($condition)
    {
        switch ($condition) {
            case 'Nuevo':
                return 1; // 1 represents 'Nuevo'
            case 'Como nuevo':
                return 2; // 2 represents 'Como nuevo'
            case 'Buen estado':
                return 3; // 3 represents 'Buen estado'
            default:
                return 0; // 0 represents 'unknown'
        }
    }

    /**
     * Get a random Spanish city.
     */
    private function getRandomCity()
    {
        $state = $this->getRandomState();
        return $this->spanishLocations[$state][array_rand($this->spanishLocations[$state])];
    }

    /**
     * Get a random Spanish state.
     */
    private function getRandomState()
    {
        return array_rand($this->spanishLocations);
    }
}
