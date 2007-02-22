//
//  TransparentBackgroundTextView.h
//  KIImageFixer
//
//  Created by Sšren Kuklau on 22/02/07.
//  Copyright 2007 chucker. All rights reserved.
//

#import <Cocoa/Cocoa.h>

@interface TransparentBackgroundTextView : NSTextView {}
- (void) awakeFromNib;
- (BOOL) isOpaque;
@end
