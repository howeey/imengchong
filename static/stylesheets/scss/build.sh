for scss_file in `ls -1 *.scss`; do
    echo "Build "${scss_file}" > "${scss_file/.scss/.css};
    scss ${scss_file} ${scss_file/.scss/.css};
done
mv -v *.css ../
