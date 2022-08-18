#!/bin/bash
set -e

SOURCE_PATH="${GITHUB_WORKSPACE}"
BUILD_PATH="${GITHUB_WORKSPACE}/build_work"
BRANCH_PREFIX="refs/heads/"
BRANCH_NAME=${GITHUB_REF#"$BRANCH_PREFIX"}
EXPORT_PATH="${BUILD_PATH}/export"
PACKAGE_PATH="${EXPORT_PATH}/zikula"
ARCHIVE_PATH="${BUILD_PATH}/archive"
PHP_BUILD="./build.php"

DOC_PATH="${PACKAGE_PATH}/docs/en"
declare -A COPY_FILES
COPY_FILES=(
    ["README_md"]="${DOC_PATH}"
    ["CHANGELOG-2_0_md"]="${DOC_PATH}"
    ["CHANGELOG-3_0_md"]="${DOC_PATH}"
    ["CHANGELOG-3_1_md"]="${DOC_PATH}"
    ["CHANGELOG-4_0_md"]="${DOC_PATH}"
    ["CHANGELOG-VENDORS-3_0_md"]="${DOC_PATH}"
    ["CHANGELOG-VENDORS-3_1_md"]="${DOC_PATH}"
    ["CHANGELOG-VENDORS-4_0_md"]="${DOC_PATH}"
    ["composer_json"]="${DOC_PATH}/dev"
    ["composer_lock"]="${DOC_PATH}/dev"
)

echo "Create required directories..."
echo "Copying sources to package directory..."
# prevent copying sub directory into itself
cp -r . /tmp/ZKTEMP
mkdir -p "${PACKAGE_PATH}" "${ARCHIVE_PATH}"
# exclude . and ..
mv /tmp/ZKTEMP/{*,.[^.]*} "${PACKAGE_PATH}"

cd "${PACKAGE_PATH}"

echo "Composer Install"
composer install --no-progress --prefer-dist --optimize-autoloader --no-scripts
echo "Post autoload dump"
composer run-script post-autoload-dump
echo "Post install command"
composer run-script post-install-cmd

echo "Generating composer_vendors file..."
${PHP_BUILD} build:generate_vendor_doc --write-to "${PACKAGE_PATH}/docs/General/VendorInformation.md"

echo "Copying docs and composer files..."
mkdir -p "${DOC_PATH}/dev"
for fileName in "${!COPY_FILES[@]}"; do
    FILE_NAME="${fileName//_/.}"
    FILE_FOLDER=${COPY_FILES[$fileName]}
    #echo "File: ${FILE_NAME}"
    #echo "Folder: ${FILE_FOLDER}"
    if [ -e "${SOURCE_PATH}/${FILE_NAME}" ]; then
        cp -f "${SOURCE_PATH}/${FILE_NAME}" "${FILE_FOLDER}/"
    fi
done

echo "Purging tests from vendors..."
${PHP_BUILD} build:purge_vendors "${PACKAGE_PATH}/vendor"

echo "Creating translation files..."
#php bin/console zikula:translation:updateconfig
#cd var/cache
#rm -rf dev prod
#cd ../..
#php -dmemory_limit=2G "${PACKAGE_PATH}/bin/console" translation:extract zikula en
#php -dmemory_limit=2G "${PACKAGE_PATH}/bin/console" zikula:translation:keytovalue

echo "Clearing cache directory..."
mv "${PACKAGE_PATH}/var/cache/.htaccess" "${PACKAGE_PATH}/var/"
rm -rf "${PACKAGE_PATH}/var/cache/"*
mv "${PACKAGE_PATH}/var/.htaccess" "${PACKAGE_PATH}/var/cache/"

echo "Clearing log directory..."
mkdir -p "${PACKAGE_PATH}/var/log"
rm -rf "${PACKAGE_PATH}/var/log/"*

echo "Setting directory permissions..."
chmod -R 0777 "${PACKAGE_PATH}/config"
chmod -R 0777 "${PACKAGE_PATH}/var/cache"
chmod -R 0777 "${PACKAGE_PATH}/var/log"

rm -rf "${PACKAGE_PATH}/.git" "${PACKAGE_PATH}/.github"

echo "Creating archives..."
ARCHIVE_BASE_PATH="${ARCHIVE_PATH}/zikula"
cd "${EXPORT_PATH}"; zip -q -D -r "${ARCHIVE_BASE_PATH}.zip" .
cd "${EXPORT_PATH}"; tar cp "zikula" > "${ARCHIVE_BASE_PATH}.tar"; gzip "${ARCHIVE_BASE_PATH}.tar"

echo "Creating MD5 and SHA1 checksums..."
CHECKSUM_PATH="${ARCHIVE_PATH}/zikula-checksums"
TMP_FILE="${CHECKSUM_PATH}.tmp"
echo "-----------------md5sums-----------------" > "${TMP_FILE}"
md5sum "${ARCHIVE_PATH}/"*.tar.gz "${ARCHIVE_PATH}/"*.zip >> "${TMP_FILE}"
echo "-----------------sha1sums-----------------" >> "${TMP_FILE}"
sha1sum "${ARCHIVE_PATH}/"*.tar.gz "${ARCHIVE_PATH}/"*.zip >> "${TMP_FILE}"

cat "${TMP_FILE}" | sed "s!${ARCHIVE_PATH}/!!g" > "${CHECKSUM_PATH}.txt"
rm -f "${TMP_FILE}"
