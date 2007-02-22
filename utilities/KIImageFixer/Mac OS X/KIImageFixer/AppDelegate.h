//
//  AppDelegate.h
//  KIImageFixer
//
//  Created by Sören Kuklau on 13/02/07.
//  Copyright 2007 chucker. All rights reserved.
//

#import <Cocoa/Cocoa.h>


@interface AppDelegate : NSObject {
	IBOutlet NSWindow * mainWindow;
	IBOutlet NSMatrix * overwriteSettingCheckboxes;
	IBOutlet NSTextField * versionText;

	IBOutlet NSTabView * textTabs;
	IBOutlet NSTextView * instructionsText;
	IBOutlet NSTextView * legalText;

	IBOutlet NSButton * proceedButton;

	IBOutlet NSPanel * processingSheet;
	IBOutlet NSTextField * processingCurrentFilename;
	IBOutlet NSTextField * processingTextualProgress;
	IBOutlet NSProgressIndicator * processingProgress;

	NSArray * selectedFiles;
}

- (IBAction) selectOriginals: (id) sender;
- (IBAction) proceed: (id) sender;

- (void) openPanelDidEnd: (NSOpenPanel *) panel returnCode: (int) returnCode contextInfo: (void  *) contextInfo;

@end
