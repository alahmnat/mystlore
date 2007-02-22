//
//  TransparentBackgroundTextView.m
//  KIImageFixer
//
//  Created by Sšren Kuklau on 22/02/07.
//  Copyright 2007 chucker. All rights reserved.
//

#import "TransparentBackgroundTextView.h"

@implementation TransparentBackgroundTextView
- (void) awakeFromNib {
	[self setDrawsBackground:NO];
	[(NSScrollView *)[self superview] setDrawsBackground:NO];
}

- (BOOL) isOpaque {
	return NO;
}
@end
