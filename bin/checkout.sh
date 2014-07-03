rm -rf src/Pixonite/BlogAdminBundle
rm -rf src/Pixonite/BlogBundle
rm -rf src/Pixonite/ScamsListBundle
rm -rf src/Pixonite/TagCloudBundle
rm -rf src/Pixonite/TrendBundle

rm -rf src/Oranges

rsync -avz rjkeller@vps3.pixonite.com:/Bundles/Pixonite/BlogAdminBundle src/Pixonite/
rsync -avz rjkeller@vps3.pixonite.com:/Bundles/Pixonite/BlogBundle src/Pixonite/
rsync -avz rjkeller@vps3.pixonite.com:/Bundles/Pixonite/ScamsListBundle src/Pixonite/
rsync -avz rjkeller@vps3.pixonite.com:/Bundles/Pixonite/TagCloudBundle src/Pixonite/
rsync -avz rjkeller@vps3.pixonite.com:/Bundles/Pixonite/TrendBundle src/Pixonite/

rsync -avz rjkeller@vps3.pixonite.com:/Bundles/Oranges src/
