# WARP-Text
The Warp-Text annotation tool

This is the initial version of the WARP-Text annotation tool.

This tool has been configured and tested on Debian Linux 9.0, php 7, and Maria DB 10.

The interface has been tested on Firefox 61 (primarily); Chrome, Edge and IE (briefly).

For more detailed instructions on installation and configuration, see the doc/ folder

Organization
- apache/ : the sample config file for apache
- db/ : empty database that should be imported
- doc/ : documentation for the installation and usage
- inc/ : various includes for the system. You should not change anything there, except for the config.inc file with the credentials
- lib/ : the actual modules of the interface, you can add custom modules there

If you use this tool, please cite

```@InProceedings{C18-2029,
  author = 	"Kovatchev, Venelin
		and Marti, Toni
		and Salamo, Maria",
  title = 	"WARP-Text: a Web-Based Tool for Annotating Relationships between Pairs of Texts",
  booktitle = 	"Proceedings of the 27th International Conference on Computational Linguistics: System Demonstrations",
  year = 	"2018",
  publisher = 	"Association for Computational Linguistics",
  pages = 	"132--136",
  location = 	"Santa Fe, New Mexico",
  url = 	"http://aclweb.org/anthology/C18-2029"
}
```

For any questions or comments, you can contact me at vkovatchev [locative] ub [sentence separator] edu
