**Vogelvereniging
**
website voor een vogelvereniging - gemaakt in laravel

In dit Github project komen de commits voor een vogelvereniging, de eerste commit is nadat het laravelproject aangemaakt is.

Het project is aangemaakt op de volgende manier:

Open een terminal in de folder waar je het project in wilt hebben. In dit geval heb ik git bash gebruikt Vervolgens type je in het terminal: "laravel new vogelvereniging". Het woord vogelvereniging is een woord waar je alles neer kunt zetten. Dit is de naam van het laravelproject. In dit geval heb ik het vogelvereniging genoemd Dan doe je enter en krijg je een aantal vragen which framework would you like to install? ik heb "none" gedaan daar which testing framework do you prefer? daar heb ik "Pest" ingevuld Do you want to install laravel Boost to improve AI assisted coding? ik heb "no" gezegd

Dan gaat hij een gedeelte installeren, na dit stukje installatie krijg je nog meer vragen (dit stukje installatie duurt ongeveer 2-3 minuten) Na dit stukje installatie gaat hij weer verder met vragen:

Which database will your application use? op school werken we altijd met MySQL. dus hier heb ik mysql ingevuld dan vraagt hij of de migrations die aangemaakt worden of die alvast uitgevoerd moeten worden. daar doe ik "ja"

dan gaat hij weer verder met installeren. dit duurt 1-2 minuten en vraagt hij nog een aantal dingen:

would you like to run NPM install and npm run build? ik doe hier ook altijd "ja" want de mogelijkheid bestaat dat er nog dingen missen van NPM en die installeerd hij er hier alvast bij. dat is voor jou makkelijker zodat je dat later niet hoeft te doen.

dan gaat hij die dingen installeren en kan jij weer even wachten tot het klaar is. zodra deze installatie klaar is, is jou website klaar om te gaan maken. er zijn wel een aantal dingen waar ik tegenaan liep bij het installeren van het project, maar dat was gelukkig snel op te lossen.

1 ding was bijvoorbeeld: Host is malformed, dat betekent dat de poort die gebruikt wordt 2 keer in de url staat, in het .env bestand wat aangemaakt wordt kan je de app_url aanpassen, zodat de poort er nog maar 1 keer instaat en dan is het prima. een ander probleem was dat de database niet automatisch aangemaakt werd, dat is wel een probleem, want anders kan de website niet runnen. nou dit los je op door de database even handmatig aan te maken. dit doe je in de database editor die je gebruikt. in mijn geval Heidisql, maar een andere die je zou kunnen gebruiken is sql workbench. de database aanmaken zou voor zover ik weet automatisch moeten gaan, maar als dat niet gebeurt dan moet je de database even handmatig aanmaken, wat zo gebeurd is

dit zijn een aantal problemen waar ik tijdens en na de installatie tegen aanliep. als je nog meer hulp nodig hebt dan zou ik even chatgpt of gemini raadplegen.

na het installeren van het laravelproject gaan we door met wat andere installaties, om te kunnen inloggen gebruiken we breeze. dat is een tool die ervoor zorgt dat je kan inloggen op een laravel applicatie. je kan het ook zelf in elkaar zetten, maar dit zorgt ook wel voor een beetje gemak.. om breeze te installeren kan je meerder commands gebruiken: composer require laravel/breeze --dev & php artisan breeze:install

bij het installeren hiervan krijg je een aantal vragen: Which breeze stack would you like to install? Blade React Vue Api

voor dit project gebruiken we Blade.

dan krijg je de vraag: Would you like dark mode support? Yes or No -> hier kiezen we Yes

dan krijgen we de laatste vraag: Which testing framework do you prefer? PHPUnit Pest

wij gebruiken Pest als testing framework voor dit vogelproject.
