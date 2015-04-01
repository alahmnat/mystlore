# Format #

The index consists of two files: an `.entryIndex` file and a `.termIndex` one.

## .entryIndex ##

The purpose of the entryIndex is to list all dictionary entries, one per line, and associated Metadata. The entries are automatically sorted alphabetically because, at the time of indexing, the dictionary itself is always already sorted. (Therefore, when creating the index and iterating through the file, the order is already correct, and will be preserved.)

A line looks like this:

```
Dictionary Entry|Byte Offset|Size in Bytes
```

A few examples:

```
Atrus|4803|300
D'ni timekeeping|18520|482
```

## .termIndex ##

The termIndex lists all noteworthy terms occurring in the dictionary, one per line; that is, it represents everything that can be (successfully) searched for. The indexing daemon needs to ensure that the termIndex is sorted alphabetically, and that no term appears multiple times.

A line looks like this:

```
Search Term|Dictionary Entry ID|Dictionary Entry ID|…
```

An example:

```
Ti'ana|129|220|438|853
```

Based on the above, the search query "Ti'ana" should give the articles with the IDs 129, 220, 438 and 853 as results. The dictionary entry IDs are actually the line numbers of the `.entryIndex` file (and, by extension, of the dictionary body file itself). Assuming the entry "Atrus" is on line 129, the index would assert that the term "Ti'ana" appears in the article "Atrus".

# Create #

The tough part about creating the index to begin with is that the dictionary body can be a huge file. As an extreme example, in raw text form, Wikipedia takes up several Gigabytes worth of articles. This makes reading the entire file and parsing the result in memory unfeasible.

Instead, the indexer should read until the end of a line, work with the line, then proceed with the following line. Fortunately, it can be assumed that one line represents one entry. Entries are neither stretched across multiple lines, nor does any line consist of multiple entries.

For each line, the indexer needs to do the following:
  1. Create a new line in `.entryIndex`:
    1. Fill the first field with the textual contents of the line `<o:hw>` element.
    1. Fill the second field with the byte offset at which the `<o:ent>` element begins.
    1. Fill the third field with the length in bytes the entire `<o:ent>` element and its children take up.
  1. Extend `.termIndex`:
    1. Check the contents of `<o:def>` elements to look for terms worth searching for
      * If not there, create lines accordingly
      * If there, append existing lines accordingly
    1. Always keep `.termIndex` sorted

Tentatively, the indexer is to be a CLI tool named `MYSTloreDictionaryIndexCreator`. `launchd` might run this tool automatically whenever the dictionary body file changes, or we might instead manually invoke it after any update.

# Parse #

When the user enters a search query, the `.termIndex` file needs to be accessed. It's easiest to create an array containing one object per line, then checking the first value in each of them, namely the search term field. If a line matches, the `.entryIndex` file needs to be read to check for each matching entry ID (i.e., line number) to get their respective offsets and sizes. Finally, if the user chooses a result, the dictionary body itself gets read.