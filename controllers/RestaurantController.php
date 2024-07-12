<?php
require_once '../config/config.php';

class RestaurantController {
    private $xmlFile = '../exo7.xml';

    public function getRestaurants() {
        $xml = simplexml_load_file($this->xmlFile);
        return $xml->Restaurant;
    }

    public function addRestaurant($restaurantData) {
        $xml = simplexml_load_file($this->xmlFile);
        $restaurants = $xml->addChild('Restaurant');
        
        $restaurants->addChild('Nom', $restaurantData['nom']);
        $restaurants->addChild('Adresse', $restaurantData['adresse']);
        $restaurants->addChild('Restaurateur', $restaurantData['restaurateur']);
        
        $description = $restaurants->addChild('Description');
        $paragraphe = $description->addChild('Paragraphe');
        $paragraphe->addChild('Texte', $restaurantData['description']['texte']);
        
        if (isset($restaurantData['description']['important'])) {
            $paragraphe->addChild('Important', $restaurantData['description']['important']);
        }
        
        if (isset($restaurantData['description']['liste'])) {
            $liste = $paragraphe->addChild('Liste');
            $liste->addAttribute('type', $restaurantData['description']['liste']['type']);
            foreach ($restaurantData['description']['liste']['items'] as $item) {
                $liste->addChild('Item', $item);
            }
        }
        
        if (isset($restaurantData['description']['image'])) {
            $image = $paragraphe->addChild('Image');
            $image->addAttribute('url', $restaurantData['description']['image']['url']);
            $image->addAttribute('position', $restaurantData['description']['image']['position']);
        }
        
        $carte = $restaurants->addChild('Carte');
        foreach ($restaurantData['carte'] as $platData) {
            $plat = $carte->addChild('Plat');
            $plat->addChild('Nom', $platData['nom']);
            $plat->addChild('Type', $platData['type']);
            $plat->addChild('Prix', $platData['prix']);
            $plat->addChild('Description', $platData['description']);
        }
        
        $menus = $restaurants->addChild('Menus');
        foreach ($restaurantData['menus'] as $menuData) {
            $menu = $menus->addChild('Menu');
            $menu->addChild('Titre', $menuData['titre']);
            $menu->addChild('Description', $menuData['description']);
            $menu->addChild('Prix', $menuData['prix']);
            $elements = $menu->addChild('Elements');
            foreach ($menuData['elements'] as $element) {
                $elements->addChild('Element', $element);
            }
        }

        $xml->asXML($this->xmlFile);
    }

    public function deleteRestaurant($nom) {
        $xml = simplexml_load_file($this->xmlFile);
        foreach ($xml->Restaurant as $restaurant) {
            if ((string)$restaurant->Nom == $nom) {
                $dom = dom_import_simplexml($restaurant);
                if ($dom) {
                    $dom->parentNode->removeChild($dom);
                }
            }
        }
        $xml->asXML($this->xmlFile);
    }

    public function updateRestaurant($oldName, $restaurantData) {
        $xml = simplexml_load_file($this->xmlFile);
        foreach ($xml->Restaurant as $restaurant) {
            if ((string)$restaurant->Nom == $oldName) {
                $restaurant->Nom = $restaurantData['nom'];
                $restaurant->Adresse = $restaurantData['adresse'];
                $restaurant->Restaurateur = $restaurantData['restaurateur'];
                
                $restaurant->Description->Paragraphe->Texte = $restaurantData['description']['texte'];
                
                if (isset($restaurantData['description']['important'])) {
                    $restaurant->Description->Paragraphe->Important = $restaurantData['description']['important'];
                } else {
                    unset($restaurant->Description->Paragraphe->Important);
                }
                
                if (isset($restaurantData['description']['liste'])) {
                    $restaurant->Description->Paragraphe->Liste['type'] = $restaurantData['description']['liste']['type'];
                    foreach ($restaurantData['description']['liste']['items'] as $item) {
                        $restaurant->Description->Paragraphe->Liste->addChild('Item', $item);
                    }
                } else {
                    unset($restaurant->Description->Paragraphe->Liste);
                }
                
                if (isset($restaurantData['description']['image'])) {
                    $restaurant->Description->Paragraphe->Image['url'] = $restaurantData['description']['image']['url'];
                    $restaurant->Description->Paragraphe->Image['position'] = $restaurantData['description']['image']['position'];
                } else {
                    unset($restaurant->Description->Paragraphe->Image);
                }
                
                unset($restaurant->Carte->Plat);
                foreach ($restaurantData['carte'] as $platData) {
                    $plat = $restaurant->Carte->addChild('Plat');
                    $plat->addChild('Nom', $platData['nom']);
                    $plat->addChild('Type', $platData['type']);
                    $plat->addChild('Prix', $platData['prix']);
                    $plat->addChild('Description', $platData['description']);
                }
                
                unset($restaurant->Menus->Menu);
                foreach ($restaurantData['menus'] as $menuData) {
                    $menu = $restaurant->Menus->addChild('Menu');
                    $menu->addChild('Titre', $menuData['titre']);
                    $menu->addChild('Description', $menuData['description']);
                    $menu->addChild('Prix', $menuData['prix']);
                    $elements = $menu->addChild('Elements');
                    foreach ($menuData['elements'] as $element) {
                        $elements->addChild('Element', $element);
                    }
                }
            }
        }
        $xml->asXML($this->xmlFile);
    }
}
?>
