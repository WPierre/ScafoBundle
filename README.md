Scafo - Scan & Forget
=====================

Summary
-------

Scafo is a simple tool helping you to sort all your paperwork. Scan all your bills, mail and such, define filters and let Scafo do all the work for you !  
You might want to redirect Scafo's output to a file management software like [Pydio](http://pyd.io) or [OwnCloud](https://owncloud.com) so you can browse and search your documents.  
Scafo is still in beta, so you can expect bugs.

How does it work ?
------------------

For details, you should check the [documentation](/Documentation/howto.md).  
Basically, you scan your papers in jpg files, put them in one of Scafo's Input folders, open Scafo's instance and run a sorting process. The scanned pages will be OCRized (the images will be converted to text) and according to the filters you defined, one of the filter will match your text and Scafo will know where to put the pdf file, how to name it and even how to extract the document's date.  
Technically, Scafo requires a *AMP server and a Symfony installation. Check out the instructions below to see how to set it up !

Help wanted
-----------

Scafo has a simple companion Java application that helps scanning your pages and sends the files directly in the good folder. Unfortunately, the only scanner library I managed to get working requires a personal (free, though) licence. Java isn't my cup of tea, so I'm looking for someone who could help me on this app so I can release it too.  
Also, although this documentation is in english, Scafo is currently only available in french.

What kind of scanner is recommended ?
-------------------------------------
Any scanner will do, but a double-sided scanner with tray loading is highly recommended. A good, not-so-expensive scanner is the Canon P-150.

Setup
-----
Note : All paths beggining with a slash refer to Symfony's root folder (ie : /var/www/scafo)

###Symfony setup
Install symfony 2.6 (or later) and set it up with a database. If you're not familiar with Symfony, please check [Composer](https://getcomposer.org/) and [Symfony](http://symfony.com/download) documentation. Basically, you have to run 
> composer create-project symfony/framework-standard-edition scafo/ 

on your webserver.

###Install VirtualHost
Set up a virtual host for your server. There's a standard Apache Virtualhost file ready to be adapted in The Scafo/ScafoBundle/Extra/VirtualHost folder.
You might want to remove Symfony's /web/.htaccess file to enable the dev environnement on the front page while Scafo is still in beta stage.

###Install Scafo
Either copy the github's content in /src/Wpierre/Scafo/ScafoBundle, or run 
> mkdir -p src/Wpierre/Scafo
> cd src/Wpierre/Scafo
> git clone adresse ScafoBundle

from the /src directory. The goal is to have Scafo's Bundle class in /src/Wpierre/Scafo/ScafoBundle

###Install Scafo's requirements
Edit your /composer.json file and add
> "whiteoctober/tcpdf-bundle": "dev-master",  
> "smalot/pdfparser": "*"

Run this command to install the dependencies
> composer update

###Enable Scafo and the dependencies in Symfony
Edit your /app/AppKernel.php and add the text below to the list of enabled bundles :
> new Wpierre\Scafo\ScafoBundle\WpierreScafoScafoBundle(),  
> new WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle(),

###Enable Scafo's routes
Edit your /app/config/routing.yml and add :
> wpierre_scafo_scafo:  
> &nbsp;&nbsp;&nbsp;&nbsp;resource: "@WpierreScafoScafoBundle/Resources/config/routing.xml"  
> &nbsp;&nbsp;&nbsp;&nbsp;prefix:   /  
> &nbsp;&nbsp;&nbsp;&nbsp;type: xml

Please mind the spaces if you're not familiar with YAML syntax (no leading space for the first line, four for the others).

###Enable Symfony's form Bootstrap theme
Edit your /app/config/config.yml and in the section "twig", add these lines :
> &nbsp;&nbsp;&nbsp;&nbsp;form:  
> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#resources: ['bootstrap_3_layout.html.twig']  
> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;resources: ['bootstrap_3_horizontal_layout.html.twig']  

Same notice as previous step, four leding spaces for first line, eight for the next ones.

###Install the assets
Scafo's bundle contains several assets, including Bootstrap and jQuery. You have to install them using :
> php app/console assets:install

You should see a line about ScafoBundle.

###Setup the database
Scafo needs a database and you configured one while installing Symfony.
If the database already exists, please use : 
> php app/console doctrine:schema:update --force

If it doesn't exist yet, please use :
> php app/console doctrine:database:create

###Test it !
According to your virtual host configuration, this might change, but if you didn't change anything, open [http://localhost:8087](http://localhost:8087).  
Scafo will run some test to check the dependencies and create your first instance. Open this instance and you can begin to put some files in one of the /app/Default_repo/Input/ folders.

