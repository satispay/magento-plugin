rm -rf .tmp
mkdir .tmp
cp -R src/* .tmp
(cd .tmp && find . -name '.DS_Store' -type f -delete)
(cd .tmp && tar czvf Satispay_Satispay-x.x.x.tgz *)
