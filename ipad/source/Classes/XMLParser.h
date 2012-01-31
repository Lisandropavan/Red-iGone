//
//  XMLParser.h
//  Redigone

#import <UIKit/UIKit.h>


@interface XMLParser : NSObject <NSXMLParserDelegate> {
	NSString * currentElement;
	NSMutableString * currentStringValue;
}

@property (nonatomic,retain) NSString * currentElement;
@property (nonatomic,retain) NSMutableString * currentStringValue;

- (void)parseXMLData : (NSData *) data;

@end
