//
//  AppDelegate.m
//  KIImageFixer
//
//  Created by Sören Kuklau on 13/02/07.
//  Copyright 2007 chucker. All rights reserved.
//

#import "AppDelegate.h"


@implementation AppDelegate
- (IBAction) selectOriginals: (id) sender {
	NSOpenPanel * openPanel = [NSOpenPanel openPanel];

	[openPanel setAllowsMultipleSelection:YES];
	[openPanel setCanChooseDirectories:NO];
	[openPanel beginSheetForDirectory:NSHomeDirectory()
		file:nil types:[NSArray arrayWithObjects:@"jpg", @"jpe", @"jpeg", nil]
		modalForWindow:mainWindow modalDelegate:self
		didEndSelector:@selector(openPanelDidEnd:returnCode:contextInfo:)
		contextInfo:NULL];
}

- (IBAction) proceed: (id) sender {
	if (selectedFiles == nil)
		return;
	if ([selectedFiles count] < 1) {
		[sender setEnabled:NO];
		return;
	}

	[processingTextualProgress setStringValue:
		[NSString stringWithFormat:@"0 of %i", [selectedFiles count]]];
	[processingProgress setDoubleValue:0.0];
	[processingProgress setMaxValue:(double)[selectedFiles count]];

	[NSApp beginSheet:processingSheet modalForWindow:mainWindow modalDelegate:self
		didEndSelector:NULL contextInfo:NULL];

	NSEnumerator * enumerator = [selectedFiles objectEnumerator];
	id currentObject;
	int i = 0;

	NSData * fileData;
	NSData * fixedFileData;
	NSString * newPath;

	while (nil != (currentObject = [enumerator nextObject])) {
		i++;
		[processingCurrentFilename setStringValue:[currentObject lastPathComponent]];
		[processingTextualProgress setStringValue:
			[NSString stringWithFormat:@"%i of %i", i, [selectedFiles count]]];
		[processingProgress incrementBy:1.0];

		fileData = [NSData dataWithContentsOfFile:currentObject];
		if ([[fileData subdataWithRange:NSMakeRange(0, 3)]
			isEqualToData:[NSData dataWithBytes:"\xff\xd8\xff" length:3]])
			continue; // proper JPEG data: no need to fix anything

		// assuming incorrect, to-be-fixed data for the remainder

		// start from the fifth (first correct) byte, and go until the very end, effectively slicing
		// off the first four bytes, which are the corrupt ones
		fixedFileData = [fileData subdataWithRange:NSMakeRange(4, [fileData length] - 4)];

		switch ([overwriteSettingCheckboxes selectedRow]) {
		case 0:
			newPath = currentObject; // overwrite mode; therefore, the paths are the same
			break;
		case 1:
			// copy mode
			newPath = [[[currentObject stringByDeletingPathExtension]
				stringByAppendingString:@" (corrected)"]
				stringByAppendingPathExtension:[currentObject pathExtension]];
			break;
		}

		[fixedFileData writeToFile:newPath atomically:YES];
	}

	[NSApp endSheet:processingSheet];
	[processingSheet orderOut:nil];
}

- (void) openPanelDidEnd: (NSOpenPanel *) panel returnCode: (int) returnCode contextInfo: (void  *) contextInfo {
	if (returnCode != NSOKButton)
		return;

	[proceedButton setEnabled:YES];

	selectedFiles = [[panel filenames] retain];
}
@end
