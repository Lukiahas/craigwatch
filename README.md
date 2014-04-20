Craigwatch
==========
The Craigwatch engine was designed to periodically scan a website and notify users of very specific changes.

- **Author:** Beau Danger Lynn-Miller
- **Website:** [http://craigwatch.com/](http://craigwatch.com/)
- **Version:** 0.0.1

Craigwatch requires the Gearman engine to be installed and running. Several checkwatch.php jobs should be running. Supervisor for Linux can be useful in accomplishing this. Once the jobs are running, checkwatches.php can be executed from the command line to run the checks. Running checkwatches.php from a cronscript can enable automated execution.

You should be able to run Craigwatch with any standard SQL server. Structure data for MySQL is included.

## Copyright and License
Craigwatch was written by Beau Danger Lynn-Miller
Craigwatch is released under the GPLv3 License. See the LICENSE file for details.