# 🔄 AutoStockPtero – WHMCS + Pterodactyl

**AutoStockPtero** est un hook PHP pour [WHMCS](https://whmcs.com) qui synchronise automatiquement la **quantité en stock** des produits d’hébergement de serveurs de jeux en fonction de la **mémoire RAM disponible sur les nœuds Pterodactyl**.

💡 Idéal pour les hébergeurs de serveurs de jeux (Minecraft, Rust, etc.) qui souhaitent :
- Éviter de vendre plus de RAM que disponible
- Améliorer la stabilité et la transparence
- Gagner du temps avec une gestion 100% automatisée

---

## ⚙️ Fonctionnalités

- 🔗 Connexion à l’API admin de **Pterodactyl**
- 📦 Lecture dynamique de la **RAM requise** depuis la **description** des produits WHMCS
- 📉 Mise à jour du stock (`qty`) dans la base de données WHMCS
- ♻️ Déclenchement :
  - Lors de la création, suppression, suspension ou réactivation d’un service
  - Une fois par jour via le `DailyCronJob` WHMCS

---

## 🧩 Exemple de produit WHMCS

Dans la **brève description du produit**, indique la RAM utilisée :

- `Serveur 4 Go`
- `Hébergement 8192 Mo`
- `Machine 2 GB`
- `RAM : 3G`

Le hook extrait automatiquement la valeur et la convertit en Mo.

---

## 🛠 Installation

1. Télécharge le fichier `auto_stock_ptero.php`
2. Place-le dans le dossier : /includes/hooks/
3. Ouvre le fichier et **modifie ces variables** avec tes données Pterodactyl :
```php
$pteroApiKey = 'YOUR_PTERO_TOKEN';
$pteroBaseUrl = 'YOUR_PTERO_API_URL';
```
4. ✅ C’est tout. La mise à jour est automatique via :
- Hook WHMCS (création, suppression, etc.)
- Cron WHMCS (DailyCronJob)

--- 

## 🧑‍💻 Auteur
Développé par SwiftHeberg pour les besoins de SwiftHeberg

## 📜 Licence
MIT – libre et open source ✌️
[LICENSE](./LICENSE)
