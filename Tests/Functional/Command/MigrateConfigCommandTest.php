<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group web-token
 */
class MigrateConfigCommandTest extends TestCase
{
    /**
     * Test command.
     */
    public function testMigrationTool()
    {
        // Given
        $kernel = $this->bootKernel();
        $app = new Application($kernel);
        $tester = new CommandTester($app->find('lexik:jwt:migrate-config'));

        // When
        $statusCode = $tester->execute([]);

        // Then
        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString('clock_skew: 0', $tester->getDisplay());
        $this->assertStringContainsString('service: lexik_jwt_authentication.encoder.web_token', $tester->getDisplay());
        $this->assertStringContainsString('signature_algorithm: RS256', $tester->getDisplay());
        $this->assertStringContainsString('key: \'{"kty":"RSA","n":"3CxMb__3E_X9zeRiBF_-ysxTpleCLrhkK0_e18R8jITARQz3c7v0SxiuMwwgrQGci2rFIAcorz-il-aoBWo1V5mB_dBtJOA0AOT4meesU68plDvtQ8X38eOPZ-WeC-gtwtZLVh8IqUhtzoEVTtxjd23XMkaTYSJIjE7wnF24260jGrxZg_AfwErYEnemXOtrIRc1Yyyha9LfM7cKsrzNAVoxTRftZ0zB6ri-n0cHBST-Or1klDgq68K2SIuFVV2QWjWczZRemJwW5hVfnLhu2e-JUQOzuH_HN4BwDmPu85W-Mz0g1ssLWsvPIEJ9fz2UPqqqEiy_LjU3PzmsSEoUXkc-G_m4Umgq61ns__6gbgJ2ukRbeUdESsBDd5O59RqHsSVTREgP6R-up4MP4SIF2xK7dSJOHlFQyt4XF-aXH3B24mf4hilyJiFWVxzkGCAUBUG6yW6v0bU2H8CUMDc--pxk7E9en9UTxpIY-1aeDmc_1ILQVlPmJzowP3AzNqdLLc74UNKSUoDGQ-QOgPNoEIHTZKvjQZ7K5DrN6vamJO0XndJyhzzjXIJ0Rr8LLCXhVyST1jU5nH7p_6HHinpS6Fr25tOcHgcxiRSdtpOi6cwzoDQn5a9_bufZT6lOzPJNJjiMcvJZ9bk-Uqg843vQC5dZb6v9JeadiXYnNNN20nk","e":"AQAB","d":"XrmgWT9i6e-XtpFfqkoysMWf550WoUsrrYa7dVFP1JT4s7yUafKfc_-2UrgRBt3-n-zbyp_J3TwflALknw_Gy118E9ssWgUr2oaofm6yMX7XALOXrOTre-JPvH-Js828gmr4FqFbdJl4xLO8myUulh9nynWaytuZIuSDmIKqGbkvtTz7tkwFHRIWTDu2E5wlhyMZEQYOnPkolnNV4vhfqwlG1MhKl3rqozXArX49gvUbe-In960CqlQnYKbGQqfyhx0xzTDcUgQ1xd_ENwUSjCkGhxh0phgzeamEjGxqTdpK7niKPF83D2VIQ7TXkXrI4P1EFnWx-wtiLY1-lctpXyn1Fl0iCnRo6LjUvcfuu6Lg8ETLfOI5NWjEXEV7fP63szuEBEC2Fyr972kVl8umMoI1gq_pY2GZOOl_mbhdHQCv6B0QqQOPW3K5JC2qvNyAfjQZIFZuPtPXgYmIL4RYpbbROoGopvwXzAYcIVmgu02wUUdjFiup8AFJg6oPlCQFUKAZl_DSkwzjTwqQ8PjdkJKX16KslxkfBBYdCprvmQwEiFIy4KkFjss0YCEMYv6WndHw_NOHp2J38_s-s8nwaVG9KzA4x7ZPYCHI5bxbuyYz8aAAw1Q6tNFcp18pPmZQPzpliTF8NK3WZgatbHl9xAo-VOxsrd5WckpIEM17kxE","p":"-Y7wSXLxEkrsyEZn9UrcfRK7GbS9g9BNUuxW8puMU0R5vINIv1gwr1ETN0jpkC-FMCZ_If0Wssm4XijjHQ47KbelI115M8biJ2oYRMkkCKDH3jXga1PgBHozt9tYNc4viaroRYmk8FG1J0OTkbFseAyHc67dY8EIYL9ySaF6VkhmUuF3H0i_j8Yl8y0sXCWKZjoBInOz_IdPfh38v0Xnps6va9VecMEuqV3xVSy77KjAwvHMwQRDAwNX5fmDJtGfJ1PfGdjoJv18hx0Szlhzen8rZbxZI6RscSthyZfxOR7K2t39shDntvp63R7LPe3ASbxiYVEb0AqRKUDNnRHGgw","q":"4dsvJ6RafE3qlCSzKMfA78t44tq5dcBnmcbLHzbdVioagg9JGds0FtVnfU4F1MBz87kyh3PBZBZ3U_IZZ4ZyMApfUyAiYoIgjE_o7bADPibprBZItoZZ5tyhnFX6kyTMh9v12TX6O60cqIj_tFhEr6YSoJ06MXqKpVkT3Tcj-Cpuh_HhDQEfT_LTVBAJ3rWHA6mQ6RfeD5iNZAbUwhZxZKsFXWWIIYgM8AwZJn7seVe1IxG5OXqnExh1zgL85bdeds0Tu-fRnLv2UOqCPQ08YCraGHMABqltWDInSwarWlUzyuFg4tQX-j9N9bj-yxCe-lpYQv6amnBj5tPH3_TSUw","dp":"23fkQ4PNFDxGHh8k36h1XZ0yY_n6TMjMp6dnE7bN4pCuyqVePcBuGFAhqRX7Ka1Q4TaJybdM1fDmrhAhI3VXfGmf1gknROycCPOZ4ixN_zR-cSJKebjqoqVhhEhnO_JXBigCWt0g66O_v4cDaTZyYOUL3iWjV030czkKZkyXTPgg3LSh0SZmKSemSkSo5WSyYRKT2tuMJwJMW2o902zDu4O67AdaJakOwy31xeUwY4FI_GgvnHOGB0lSbNJQj7v7zldJNe72wwtcD2r3Ffbdn5Xk8XSBpAG-yIvRVLvGDWjSF0bxDD8nuFhx4rJpJM5Is8_zaQgugHg6juAJsx9lxw","dq":"IKcyuxV864nMR1zC9jti_og5UvryYz7M-6ONDFc_SszNhk41cGKLtl1mF-ym1Sp52RvGXWTz6ceBuwY-fAQpEB7_xyHXNsy_benDsFGJNnwjvnh-TL1B1CnDx7l6f7mLRH0dnyi5o9UUVp1v8p_sVkS5XrU8i5i-4MbvI0Vskt13m3nx4pJt934Q5Y9oDeXKvlHOnJSRy0lv7605J1JdVIORQ_6A3vAvhqkJHdKt16FBk-9lCxVbgFxB8-XksEWBh8WAe-M5H-Lg6rPs3mzCdNjdLTm7IDtwjpa5rZQqQ-YbldJd0o19ZfWvDL6RP8SIZ4OWTSFIMtna561osU1Q9w","qi":"Sj1BRYQeon6jz2h45mCxJ6-KRcqWvr9gN2JLfm0U56CmAly4TPaPOs8ZLXN-Hlifn2_VxfwdQpNJPHAq7BuF0YNk2DidvFueeRXpCrGSrU-Ho_k-hNzD87RHo514H36sKpOj4i4zF8B4yLQMp1sLAJUbpe44NVeLWHKlTW-8o_QdYiwxTx3-fyKq62Cwn7R1-AxaDe_JyGeE87e8mo6iADz4ECOH_NAxawd_qoAD6Eo-xCEtE-vPuvU9qSx0szxsdA7Oox6DYtExMPRhWKCe6sYhtOthoZeRohk57md0sngw7FjNi7NHX4nWAIdnNoURAC42wr9oJljOejv-K0YyYA","use":"sig","alg":"RS256"}\'', $tester->getDisplay());
        $this->assertStringContainsString('allowed_signature_algorithms:' . PHP_EOL . '                - RS256', $tester->getDisplay());
        $this->assertStringContainsString('keyset: \'{"keys":[{"kty":"RSA","n":"3CxMb__3E_X9zeRiBF_-ysxTpleCLrhkK0_e18R8jITARQz3c7v0SxiuMwwgrQGci2rFIAcorz-il-aoBWo1V5mB_dBtJOA0AOT4meesU68plDvtQ8X38eOPZ-WeC-gtwtZLVh8IqUhtzoEVTtxjd23XMkaTYSJIjE7wnF24260jGrxZg_AfwErYEnemXOtrIRc1Yyyha9LfM7cKsrzNAVoxTRftZ0zB6ri-n0cHBST-Or1klDgq68K2SIuFVV2QWjWczZRemJwW5hVfnLhu2e-JUQOzuH_HN4BwDmPu85W-Mz0g1ssLWsvPIEJ9fz2UPqqqEiy_LjU3PzmsSEoUXkc-G_m4Umgq61ns__6gbgJ2ukRbeUdESsBDd5O59RqHsSVTREgP6R-up4MP4SIF2xK7dSJOHlFQyt4XF-aXH3B24mf4hilyJiFWVxzkGCAUBUG6yW6v0bU2H8CUMDc--pxk7E9en9UTxpIY-1aeDmc_1ILQVlPmJzowP3AzNqdLLc74UNKSUoDGQ-QOgPNoEIHTZKvjQZ7K5DrN6vamJO0XndJyhzzjXIJ0Rr8LLCXhVyST1jU5nH7p_6HHinpS6Fr25tOcHgcxiRSdtpOi6cwzoDQn5a9_bufZT6lOzPJNJjiMcvJZ9bk-Uqg843vQC5dZb6v9JeadiXYnNNN20nk","e":"AQAB","use":"sig","alg":"RS256"},{"use":"sig","alg":"RS256","kid":"', $tester->getDisplay());
    }
}
