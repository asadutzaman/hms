# Retrofit + OkHttp
-dontwarn okhttp3.**
-dontwarn okio.**
-dontwarn retrofit2.**
-keepattributes Signature, InnerClasses, EnclosingMethod
-keepattributes RuntimeVisibleAnnotations, RuntimeVisibleParameterAnnotations

# kotlinx.serialization — keep @Serializable models & generated serializers
-keepattributes *Annotation*, RuntimeVisible*Annotations, AnnotationDefault
-keepclassmembers class **$$serializer { *; }
-keepclasseswithmembers class com.cedarview.hms.** {
    kotlinx.serialization.KSerializer serializer(...);
}
-keep,includedescriptorclasses class com.cedarview.hms.**$$serializer { *; }
