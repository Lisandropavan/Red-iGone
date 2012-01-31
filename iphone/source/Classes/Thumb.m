//
//  Thumb.m
//  Redigone


#import "Thumb.h"


@implementation Thumb

@synthesize th;
@synthesize name;

- (id) init {
	return self;
}

- (void) dealloc {
	[th release];
	[name release];
	[super dealloc];
}

@end
