Scafo - Scan & Forget : How To
==============================

Getting started
---------------

1. Install and setup the platform, using this documentation.
2. On your freshly installed instance, check out the filter section. You can import standard filters ( EDF bills, GDF bills, taxes...) using the import button, or even set new ones. See how filters work in the section below.
3. Scan some papers and put the jpeg files in one of the input folders (located by default in Scafo's root dir/app/Default_repo/Input). To understand how to scan and where you should put your files, please see the appropriate section below.
4. On the instance's page, you'll have several buttons, one for each of the Input's subfolders. You'll see a badge containing a number where folders have files in them. Click on the corresponding button to start processing the scanned files.
Warning : The OCR (image to text) process can be pretty long according to your computer's spec. Consider between 20 to 60 seconds per jpg file.
5. If the popup shows any error, you can click on the link to see the error to get the detail. Please see the troubleshooting section below.
6. You pdf files are located in the output folder, or one of it's subfolder (default in Scafo's root dir/app/Default_repo/Output)
7. If a file lands in the Unindexed folder, that means that no filter matched one of your filters. Please check the corresponding .txt file, your filters and the troubleshooting section for help.

Filters
-------

How do they work ?  
When jpg files are processed, their text is extracted and assembled. This text is matched against each of your filters in the corresponding order. When a filter matches your text, the text is renamed against the filter's rule and will be placed in the filter's folder.
A filter can use one of three matching methods :
  * Search with individual words : It will search for all the words you entered in the file's text. If all words have been found, regardless of their position, the filter matches.
  * Google syntax : You can group words using quotation marks (") and use "-" in front of words or groups to search the text NOT containing these words or groups
  * Regexp : If your text matches a regexp, the filter will be selected.

Input folders
-------------

Here is the Input folder's list of subfolder.
* By_1, By_2, By_3, By_4 : Scafo will take 1, 2, 3 or 4 files at once and merge them in a pdf file
* By_separator : Scafo will keep merging files until it recognizes the document separator. You have to print the WPierre/Scafo/ScafoBundle/Extra/Doc Separator/Doc Separator.pdf file and scan it after each document in order to separate the documents in the pages flow.
* Refilter_PDF : Put here your PDF files so they can be matched against your filters again.
* PicturesToCBZ : Used to mass-convert jpg comics album in CBZ file format. In the Input folder, put each album in a subfolder, named after the album's name. The name of the folder will be the CBZ file's name.

Troubleshooting
---------------

Q: I got an error, what should I do ?  
A: Read the error report by clicking the link with the error. Most of the time, the error is about Scafo not allowed to write in a folder. Check your Temp, Output directory for rights. If you know your document will match a filter, also check the filter's output folder.

Q: I got an error and my files disapeared from the Input folder. Are they lost ?  
A: No, they are in your instance's Temp folder (default in Scafo's root dir/app/Default_repo/Temp). If you correct Scafo's error report, click on any button in the instance's main page (preferabilly a button with no number) and Scafo will process again your files.

Q: Where can I look for errors ?  
A: Look in Scafo's log folder (/app/logs) and also check your Apache/NGinx/Web server's log

Q: My file always lands in the Unindexed folder and the debug .txt file show that the words my filter uses are badly recognized  
A: Please check your jpgs file resolution. When you scan, use at least a 200 dpi resolution. If your paper contains small prints, don't hesitate to scan with a 300 or higher resolution. Note that a higher resolution means a heavier PDF file and longer processing time.